<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    public function index(CartService $cart)
    {
        $cartItems = $cart->getEnrichedCart();

        try {
            $now = Carbon::now();
            $coupons = Coupon::whereRaw('"is_active" = true')
                ->where(function($q) use ($now) { $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now); })
                ->where(function($q) use ($now) { $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now); })
                ->get();
            Cache::put('available_coupons', $coupons, now()->addMinutes(10));

            $shipping = null;
            if (auth()->check()) {
                $previousOrders = auth()->user()->orders()->where('status', '!=', 'cancelled')->count();
                $shipping = ($previousOrders === 0) ? 0 : config('store.default_shipping', 30);
            }

            $hasActiveCoupons = $coupons->isNotEmpty();
        } catch (\PDOException $e) {
            $coupons = Cache::get('available_coupons', collect());
            $shipping = config('store.default_shipping', 30);
            $hasActiveCoupons = $coupons->isNotEmpty();
        }

        return view('shop.cart', compact('cartItems', 'cart', 'coupons', 'shipping', 'hasActiveCoupons'));
    }

    public function add(Request $request, ProductVariant $variant, CartService $cart)
    {
        $quantity = $request->input('quantity', 1);
        $cart->add($variant->id, $quantity);
        return response()->json([
            'message' => __('global.added_to_cart'),
            'cartCount' => $cart->count()
        ]);
    }

    public function buyNow(Request $request, ProductVariant $variant, CartService $cart)
    {
        $quantity = $request->input('quantity', 1);
        $cart->clear();
        $cart->add($variant->id, $quantity);
        return response()->json([
            'cartCount' => $cart->count()
        ]);
    }

    public function update(Request $request, $variantId, CartService $cart)
    {
        $cart->updateQuantity($variantId, $request->quantity);
return response()->json([
            'total' => (int) round($cart->total()),
            'cartCount' => $cart->count()
        ]);
    }

    public function remove($variantId, CartService $cart)
    {
        $cart->remove($variantId);
return response()->json([
            'message' => __('global.removed_from_cart'),
            'cartCount' => $cart->count(),
            'total' => (int) round($cart->total())
        ]);
    }

    public function applyCoupon(Request $request, CartService $cart)
    {
        $code = $request->input('code');
        if (!$code) return response()->json(['message' => __('global.coupon_error')], 422);

        $coupon = $cart->applyCouponByCode($code);
        if (!$coupon) {
            return response()->json(['message' => __('global.coupon_error')], 422);
        }

        return response()->json([
            'message' => __('global.coupon_success'),
            'cartCount' => $cart->count(),
            'total' => (int) round($cart->total()),
            'coupon' => ['code' => $coupon->code, 'type' => $coupon->type, 'value' => $coupon->value]
        ]);
    }

    public function removeCoupon(CartService $cart)
    {
        $cart->removeCoupon();
        return response()->json([
            'message' => __('global.coupon_removed'),
            'cartCount' => $cart->count(),
            'total' => (int) round($cart->total())
        ]);
    }
}
