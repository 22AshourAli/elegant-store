<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(CartService $cart)
    {
        $products = auth()->user()->wishlist()->with('media', 'variants')->paginate(12)->withQueryString();

        if ($products->count() === 0 && $products->total() > 0 && $products->currentPage() > 1) {
            return redirect()->to(request()->fullUrlWithQuery(['page' => 1]));
        }

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

        return view('shop.wishlist', compact('products', 'wishlistIds', 'cartProductIds'));
    }

    public function toggle(Product $product, Request $request)
    {
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
    }
}
