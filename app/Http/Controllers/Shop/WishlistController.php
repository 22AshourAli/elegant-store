<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\CursorService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request, CartService $cart)
    {
        $result = CursorService::applyCursor(
            auth()->user()->wishlist()->with('media', 'variants')->reorder(),
            $request->input('cursor'),
            'created_at',
            'desc',
            12
        );
        $products = $result['data'];

        $wishlistIds = auth()->user()->wishlist()->pluck('product_id')->toArray();

        $cartProductIds = [];
        $cartItems = $cart->getCart();
        $variantIds = array_keys($cartItems);
        if (!empty($variantIds)) {
            $cartProductIds = ProductVariant::whereIn('id', $variantIds)
                ->pluck('product_id')
                ->unique()
                ->values()
                ->toArray();
        }

        return view('shop.wishlist', compact('products', 'wishlistIds', 'cartProductIds', 'result'));
    }

    public function toggle(Product $product, Request $request)
    {
        try {
            $user = auth()->user();
            $exists = $user->wishlist()->where('product_id', $product->id)->exists();

            if ($exists) {
                $user->wishlist()->detach($product->id);
                $added = false;
                $message = __('global.wishlist_removed');
            } else {
                $user->wishlist()->attach($product->id);
                $added = true;
                $message = __('global.wishlist_added');
            }

            return response()->json([
                'added' => $added,
                'message' => $message,
                'count' => $user->wishlist()->count(),
            ]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['error' => __('global.server_error')], 500);
        }
    }
}
