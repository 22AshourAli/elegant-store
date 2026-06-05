<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;
use App\Models\Coupon;
use Carbon\Carbon;

class CartService
{
    public function getCart()
    {
        return Session::get('cart', []);
    }

    public function add($variantId, $quantity = 1)
    {
        $cart = $this->getCart();
        if (isset($cart[$variantId])) {
            $cart[$variantId]['quantity'] += $quantity;
        } else {
            $variant = ProductVariant::with('product')->findOrFail($variantId);
            $cart[$variantId] = [
                'variant_id' => $variantId,
                'product_name' => $variant->product->name,
                'color' => $variant->color,
                'size' => $variant->size,
                'price' => $variant->current_price,
                'image' => $variant->getFirstMediaUrl('variant_images', 'thumb') ?: ($variant->getFirstMediaUrl('variant_images') ?: ($variant->product->getFirstMediaUrl('product_images', 'thumb') ?: $variant->product->getFirstMediaUrl('product_images'))),
                'quantity' => $quantity,
            ];
        }
        Session::put('cart', $cart);
        return $cart;
    }

    public function remove($variantId)
    {
        $cart = $this->getCart();
        unset($cart[$variantId]);
        Session::put('cart', $cart);
    }

    public function updateQuantity($variantId, $quantity)
    {
        $cart = $this->getCart();
        if ($quantity <= 0) {
            $this->remove($variantId);
        } elseif (isset($cart[$variantId])) {
            $cart[$variantId]['quantity'] = (int)$quantity;
        }
        Session::put('cart', $cart);
    }

    public function total()
    {
        $base = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $this->getCart()));
        $coupon = $this->getAppliedCoupon();
        if (!$coupon) return $base;

        // check min order
        if ($coupon->min_order_amount && $base < $coupon->min_order_amount) return $base;

        if ($coupon->type === 'percent') {
            $discount = ($coupon->value / 100) * $base;
        } else {
            $discount = $coupon->value;
        }

        $total = max(0, $base - $discount);
        return $total;
    }

    public function baseTotal()
    {
        return array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $this->getCart()));
    }

    public function getDiscount()
    {
        $base = $this->baseTotal();
        $coupon = $this->getAppliedCoupon();
        if (!$coupon) return 0;
        if ($coupon->min_order_amount && $base < $coupon->min_order_amount) return 0;
        if ($coupon->type === 'percent') {
            return ($coupon->value / 100) * $base;
        }
        return $coupon->value;
    }

    public function applyCouponByCode(string $code)
    {
        $now = Carbon::now();
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->where(function($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            })
            ->first();

        if (!$coupon) return null;
        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) return null;

        $coupon->increment('times_used');
        Session::put('coupon', ['id' => $coupon->id, 'code' => $coupon->code]);
        return $coupon;
    }

    public function removeCoupon()
    {
        Session::forget('coupon');
    }

    public function clear()
    {
        Session::forget('cart');
        Session::forget('coupon');
    }

    public function getAppliedCoupon()
    {
        $c = Session::get('coupon');
        if (!$c || empty($c['id'])) return null;
        return Coupon::find($c['id']);
    }

    public function count()
    {
        return array_sum(array_column($this->getCart(), 'quantity'));
    }
}
