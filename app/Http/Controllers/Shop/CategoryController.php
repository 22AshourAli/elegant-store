<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function show($slug)
    {
        $version = Cache::get('cache_version', 1);
        $cacheKey = 'category_' . $version . '_' . md5($slug . '_' . request()->fullUrl());

        try {
            $category = Category::where('slug', $slug)->with('children')->firstOrFail();
            Cache::put($cacheKey . '_cat', $category, now()->addMinutes(10));

            $categoryIds = [$category->id];
            if ($category->children->count() > 0) {
                $categoryIds = array_merge($categoryIds, $category->children->pluck('id')->toArray());
            }

            $query = Product::whereIn('category_id', $categoryIds)
                ->active()
                ->with('media', 'variants');

            $sort = request('sort', 'latest');
            if ($sort === 'price_asc') {
                $query->orderByRaw('COALESCE(sale_price, base_price) ASC');
            } elseif ($sort === 'price_desc') {
                $query->orderByRaw('COALESCE(sale_price, base_price) DESC');
            } else {
                $query->latest();
            }

            $products = $query->paginate(12)->withQueryString();
            Cache::put($cacheKey . '_prods', $products, now()->addMinutes(10));

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

            \SEOMeta::setTitle($category->name . ' - Elegant Store');
            \SEOMeta::setDescription('تسوق أحدث منتجات ' . $category->name . ' بأفضل الأسعار.');
            \OpenGraph::setTitle($category->name . ' - Elegant Store');

            return view('shop.category', compact('category', 'products', 'wishlistIds', 'cartProductIds'));
        } catch (\PDOException $e) {
            $products = Cache::get($cacheKey . '_prods');
            $category = Cache::get($cacheKey . '_cat');

            if (!$products || !$category) {
                return view('shop.offline');
            }

            $wishlistIds = [];
            $cartProductIds = [];

            return view('shop.category', compact('category', 'products', 'wishlistIds', 'cartProductIds'));
        }
    }
}
