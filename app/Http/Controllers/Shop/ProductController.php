<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function show($slug)
    {
        $cacheKey = 'product_' . md5($slug);

        try {
            $product = Product::where('slug', $slug)
                ->where('is_active', true)
                ->with(['variants' => function($q) {
                    $q->where('is_active', true);
                }, 'variants.branches', 'variants.media', 'media', 'category'])
                ->firstOrFail();

            Cache::put($cacheKey, $product, now()->addMinutes(10));
        } catch (\PDOException $e) {
            $product = Cache::get($cacheKey);

            if (!$product) {
                return view('shop.offline');
            }
        }

        $colors = $product->variants->pluck('color')->unique()->filter()->values();
        $sizes = $product->variants->pluck('size')->unique()->filter()->values();

        $colorImages = [];
        foreach ($colors as $color) {
            $key = is_string($color) ? mb_strtolower(trim($color)) : $color;
            $variantWithImage = $product->variants
                ->where('color', $color)
                ->first(fn($v) => $v->image_url || $v->hasMedia('variant_images'));
            $colorImages[$key] = $variantWithImage
                ? $variantWithImage->imageUrl()
                : $product->firstImageUrl();
        }

        \SEOMeta::setTitle($product->name . ' | Elegant Store');
        \SEOMeta::setDescription($product->meta_description ?? mb_substr(strip_tags($product->description), 0, 160));
        \OpenGraph::setTitle($product->name);
        \OpenGraph::addImage($product->firstImageUrl());

        return view('shop.product', compact('product', 'colors', 'sizes', 'colorImages'));
    }
}
