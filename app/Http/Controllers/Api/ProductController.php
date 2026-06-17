<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductDetailResource;
use App\Models\Product;
use App\Services\CursorService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = min((int) $request->input('per_page', 20), 100);
            $cursor = $request->input('cursor');
            $sort = $request->input('sort', 'latest');

            $sortColumn = 'created_at';
            $direction = 'desc';

            if ($sort === 'price_asc') {
                $sortColumn = 'base_price';
                $direction = 'asc';
            } elseif ($sort === 'price_desc') {
                $sortColumn = 'base_price';
                $direction = 'desc';
            } elseif ($sort === 'name_az') {
                $sortColumn = 'name';
                $direction = 'asc';
            } elseif ($sort === 'name_za') {
                $sortColumn = 'name';
                $direction = 'desc';
            }

            $query = Product::with(['variants', 'category', 'colorImages'])
                ->active();

            $result = CursorService::applyCursor(
                $query,
                $cursor,
                $sortColumn,
                $direction,
                $perPage,
            );

            return response()->json([
                'data' => ProductCardResource::collection($result['data']),
                'next_cursor' => $result['next_cursor'],
                'prev_cursor' => $result['prev_cursor'],
                'has_more' => $result['has_more'],
                'per_page' => $result['per_page'],
            ]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['error' => __('global.server_error')], 500);
        }
    }

    public function show(Product $product)
    {
        try {
            $product->load(['variants.branches', 'category', 'colorImages']);

            if (!$product->is_active) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return new ProductDetailResource($product);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['error' => __('global.server_error')], 500);
        }
    }
}
