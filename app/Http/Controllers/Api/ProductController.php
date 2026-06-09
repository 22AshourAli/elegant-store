<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['variants.branches', 'category', 'colorImages'])
            ->active()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $products->through(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'base_price' => (float) $p->base_price,
                'current_price' => (float) $p->current_price,
                'is_on_sale' => $p->is_on_sale,
                'image' => $p->firstImageUrl(),
                'colors' => $p->variants->pluck('color')->filter()->unique()->values(),
                'has_variants' => $p->has_variants,
                'category_id' => $p->category_id,
            ]),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['variants.branches', 'category', 'colorImages']);

        if (!$product->is_active) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return new ProductDetailResource($product);
    }
}
