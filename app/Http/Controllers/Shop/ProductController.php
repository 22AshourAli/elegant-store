<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['variants' => function($q) {
                $q->where('is_active', true);
            }, 'variants.branches', 'variants.media', 'media', 'category'])
            ->firstOrFail();

        $colors = $product->variants->pluck('color')->unique()->filter()->values();
        $sizes = $product->variants->pluck('size')->unique()->filter()->values();

        $colorImages = [];
        foreach ($colors as $color) {
            $key = is_string($color) ? mb_strtolower(trim($color)) : $color;
            // Find the first variant of this color that actually has a variant image
            $variantWithImage = $product->variants
                ->where('color', $color)
                ->first(fn($v) => $v->hasMedia('variant_images'));
            $colorImages[$key] = $variantWithImage
                ? $variantWithImage->getFirstMediaUrl('variant_images', 'responsive')
                : $product->getFirstMediaUrl('product_images', 'responsive');
        }

        // SEO
        \SEOMeta::setTitle($product->name . ' | Elegant Store');
        \SEOMeta::setDescription($product->meta_description ?? substr(strip_tags($product->description), 0, 160));
        \OpenGraph::setTitle($product->name);
        \OpenGraph::addImage($product->getFirstMediaUrl('product_images'));

        return view('shop.product', compact('product', 'colors', 'sizes', 'colorImages'));
    }
}
