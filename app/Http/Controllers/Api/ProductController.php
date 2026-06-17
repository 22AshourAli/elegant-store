<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductDetailResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['variants', 'category', 'colorImages'])
            ->active()
            ->latest()
            ->paginate(20);

        return ProductCardResource::collection($products);
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
