<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        $products = $this->loadProducts();
        $categories = $this->loadCategories();

        if ($products === null || $categories === null) {
            return view('shop.offline');
        }

        if (request()->ajax() || request()->has('ajax')) {
            $html = view('shop.partials.product-grid', compact('products'))->render();
            return response()->json(['html' => $html]);
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

        return view('shop.home', compact('products', 'categories', 'slides', 'wishlistIds', 'cartProductIds'));
    }

    private function loadProducts()
    {
        $cacheKey = 'homepage_' . md5(request()->fullUrl());

        $products = Cache::get($cacheKey);
        if ($products) {
            return $products;
        }

        try {
            $query = Product::with('media', 'variants')->whereRaw('"is_active" = true');

            if ($search = request('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            if (request('category_id')) {
                $query->where('category_id', request('category_id'));
            }

            if (request('min_price')) {
                $query->whereRaw('COALESCE(sale_price, base_price) >= ?', [request('min_price')]);
            }
            if (request('max_price')) {
                $query->whereRaw('COALESCE(sale_price, base_price) <= ?', [request('max_price')]);
            }

            switch (request('sort')) {
                case 'price_asc':
                    $query->orderByRaw('COALESCE(sale_price, base_price) asc');
                    break;
                case 'price_desc':
                    $query->orderByRaw('COALESCE(sale_price, base_price) desc');
                    break;
                case 'name_az':
                    $query->orderBy('name', 'asc');
                    break;
                default:
                    $query->latest();
                    break;
            }

            $products = $query->paginate(12)->appends(request()->query());
            Cache::put($cacheKey, $products, now()->addMinutes(10));

            if (!Cache::has('homepage_default')) {
                Cache::put('homepage_default', $products, now()->addMinutes(10));
            }

            return $products;
        } catch (\PDOException $e) {
            Log::warning('HomeController: DB unreachable, serving from cache', ['error' => $e->getMessage()]);
            return Cache::get('homepage_default');
        }
    }

    private function loadCategories()
    {
        try {
            $categories = Cache::remember('categories_all', 3600, function () {
                return Category::whereRaw('"is_active" = true')
                    ->whereNull('parent_id')
                    ->with('children')
                    ->get();
            });
            return $categories;
        } catch (\PDOException $e) {
            Log::warning('HomeController: DB unreachable for categories', ['error' => $e->getMessage()]);
            return Cache::get('categories_all');
        }
    }
}
