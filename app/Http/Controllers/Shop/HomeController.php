<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\CursorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function search(Request $request)
    {
        try {
            $searchTerm = $request->get('search');

            if (!$searchTerm) {
                return redirect()->route('home');
            }

            $query = Product::with('media', 'variants')
                ->where('is_active', true)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });

            $result = CursorService::applyCursor(
                $query,
                $request->input('cursor'),
                'created_at',
                'desc',
                12,
            );

            $products = $result['data'];

            $wishlistIds = [];
            $cartProductIds = [];
            if (auth()->check()) {
                $wishlistIds = auth()->user()->wishlist()->pluck('product_id')->toArray();
            }
            $cart = app(CartService::class);
            $variantIds = array_keys($cart->getCart());
            if (!empty($variantIds)) {
                $cartProductIds = ProductVariant::whereIn('id', $variantIds)->pluck('product_id')->toArray();
            }

            return view('shop.search', compact(
                'products', 'wishlistIds', 'cartProductIds', 'result'
            ) + ['query' => $searchTerm]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            flash()->error(__('global.server_error'));
            return redirect()->back();
        }
    }

    public function index(Request $request)
    {
        $result = $this->loadProducts($request);
        $categories = $this->loadCategories();

        if ($result === null || $categories === null) {
            return view('shop.offline');
        }

        $products = $result['data'];
        $nextCursor = $result['next_cursor'];
        $prevCursor = $result['prev_cursor'];
        $hasMore = $result['has_more'];

        if ($request->ajax() || $request->has('ajax')) {
            $html = view('shop.partials.product-grid', compact(
                'products', 'nextCursor', 'prevCursor', 'hasMore'
            ))->render();

            return response()->json([
                'html' => $html,
                'next_cursor' => $nextCursor,
                'prev_cursor' => $prevCursor,
                'has_more' => $hasMore,
            ]);
        }

        $wishlistIds = [];
        $cartProductIds = [];
        if (auth()->check()) {
            $wishlistIds = auth()->user()->wishlist()->pluck('product_id')->toArray();
        }
        $cart = app(CartService::class);
        $variantIds = array_keys($cart->getCart());
        if (!empty($variantIds)) {
            $cartProductIds = ProductVariant::whereIn('id', $variantIds)->pluck('product_id')->toArray();
        }

        $slides = [
            [
                'title' => __('global.slide_1_title'),
                'description' => __('global.slide_1_desc'),
                'cta' => __('global.shop_now'),
                'link' => route('shop.category', 'men-clothing'),
                'gradient' => 'from-indigo-600 to-purple-700 dark:from-gray-800 dark:to-gray-900',
            ],
            [
                'title' => __('global.slide_2_title'),
                'description' => __('global.slide_2_desc'),
                'cta' => __('global.shop_now'),
                'link' => route('shop.category', 'men-pants'),
                'gradient' => 'from-rose-600 to-orange-600 dark:from-gray-800 dark:to-gray-900',
            ],
            [
                'title' => __('global.slide_3_title'),
                'description' => __('global.slide_3_desc'),
                'cta' => __('global.learn_more'),
                'link' => '#featured',
                'gradient' => 'from-emerald-600 to-teal-600 dark:from-gray-800 dark:to-gray-900',
            ],
        ];

        \SEOMeta::setTitle(__('global.hero_title') . ' - ' . config('app.name'));
        \SEOMeta::setDescription(__('global.hero_desc'));
        \OpenGraph::setTitle(__('global.hero_title') . ' - ' . config('app.name'));
        \OpenGraph::setDescription(__('global.hero_desc'));

        return view('shop.home', compact(
            'products', 'categories', 'slides',
            'wishlistIds', 'cartProductIds',
            'nextCursor', 'prevCursor', 'hasMore',
        ));
    }

    private function loadProducts(Request $request): ?array
    {
        try {
            $query = Product::with('media', 'variants')->where('is_active', true);

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            if ($request->input('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            if ($request->input('min_price')) {
                $query->whereRaw('COALESCE(sale_price, base_price) >= ?', [(float) $request->input('min_price')]);
            }
            if ($request->input('max_price')) {
                $query->whereRaw('COALESCE(sale_price, base_price) <= ?', [(float) $request->input('max_price')]);
            }

            $sort = $request->input('sort', 'latest');
            $sortColumn = 'created_at';
            $sortDir = 'desc';

            if ($sort === 'price_asc') {
                $sortColumn = 'base_price';
                $sortDir = 'asc';
            } elseif ($sort === 'price_desc') {
                $sortColumn = 'base_price';
                $sortDir = 'desc';
            } elseif ($sort === 'name_az') {
                $sortColumn = 'name';
                $sortDir = 'asc';
            }

            return CursorService::applyCursor(
                $query,
                $request->input('cursor'),
                $sortColumn,
                $sortDir,
                12,
            );

        } catch (\PDOException $e) {
            Log::warning('HomeController: DB unreachable', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function loadCategories()
    {
        try {
            return Cache::remember('categories_all', 3600, function () {
                return Category::where('is_active', true)
                    ->whereNull('parent_id')
                    ->with('children')
                    ->get();
            });
        } catch (\PDOException $e) {
            Log::warning('HomeController: categories DB unreachable', ['error' => $e->getMessage()]);
            return Cache::get('categories_all');
        }
    }

    public function loadMore(Request $request)
    {
        try {
            $result = $this->loadProducts($request);

            if (!$result) {
                return response()->json(['html' => '', 'has_more' => false, 'next_cursor' => null]);
            }

            $products = $result['data'];

            $html = view('shop.partials.product-grid-cards', compact('products'))->render();

            return response()->json([
                'html' => $html,
                'next_cursor' => $result['next_cursor'],
                'has_more' => $result['has_more'],
            ]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['error' => __('global.server_error')], 500);
        }
    }
}
