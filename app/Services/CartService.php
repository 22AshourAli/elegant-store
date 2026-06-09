<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Memoized coupon instance for the current request lifecycle.
     * Prevents duplicate DB queries when total(), getDiscount(), etc.
     * are all called on the same request.
     */
    private ?Coupon $resolvedCoupon = null;

    /**
     * Return the raw session cart (variant_id => quantity).
     * Prices are NOT stored in the session; they are always read live.
     */
    public function getCart(): array
    {
        return Session::get('cart', []);
    }

    /**
     * Add a variant to the cart. Only variant_id and quantity are persisted.
     * All other display fields are hydrated from the DB on the fly.
     */
    public function add(int $variantId, int $quantity = 1): array
    {
        $cart = $this->getCart();
        if (isset($cart[$variantId])) {
            $cart[$variantId]['quantity'] += $quantity;
        } else {
            $cart[$variantId] = [
                'variant_id' => $variantId,
                'quantity'   => $quantity,
            ];
        }
        Session::put('cart', $cart);
        return $this->getEnrichedCart();
    }

    public function remove(int $variantId): void
    {
        $cart = $this->getCart();
        unset($cart[$variantId]);
        Session::put('cart', $cart);
    }

    public function updateQuantity(int $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($variantId);
            return;
        }
        $cart = $this->getCart();
        if (isset($cart[$variantId])) {
            $cart[$variantId]['quantity'] = $quantity;
            Session::put('cart', $cart);
        }
    }

    /**
     * Return a fully enriched cart array, fetching live prices & metadata
     * from the database. This eliminates stale-price bugs.
     */
    public function getEnrichedCart(): array
    {
        $raw = $this->getCart();
        if (empty($raw)) {
            return [];
        }

        $variantIds = array_keys($raw);
        $variants   = ProductVariant::with('product', 'media')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $enriched = [];
        foreach ($raw as $variantId => $item) {
            if (!isset($variants[$variantId])) {
                // Variant was deleted; remove it silently.
                $this->remove($variantId);
                continue;
            }
            $variant              = $variants[$variantId];
            $enriched[$variantId] = [
                'variant_id'   => $variantId,
                'product_name' => $variant->product->name,
                'color'        => $variant->color,
                'size'         => $variant->size,
                'price'        => $variant->current_price,
                'image'        => $variant->getFirstMediaUrl('variant_images', 'thumb')
                    ?: ($variant->getFirstMediaUrl('variant_images')
                        ?: ($variant->product->getFirstMediaUrl('product_images', 'thumb')
                            ?: $variant->product->getFirstMediaUrl('product_images'))),
                'quantity'     => $item['quantity'],
            ];
        }
        return $enriched;
    }

    /**
     * Cart subtotal (before coupon), computed from live DB prices.
     */
    public function baseTotal(): float
    {
        return array_sum(
            array_map(fn($i) => $i['price'] * $i['quantity'], $this->getEnrichedCart())
        );
    }

    /**
     * Final total after any applied coupon discount.
     */
    public function total(): float
    {
        $base   = $this->baseTotal();
        $coupon = $this->getAppliedCoupon();
        if (!$coupon) {
            return $base;
        }
        if ($coupon->min_order_amount && $base < $coupon->min_order_amount) {
            return $base;
        }
        return max(0, $base - $this->calculateDiscount($base, $coupon));
    }

    /**
     * The monetary discount amount.
     */
    public function getDiscount(): float
    {
        $base   = $this->baseTotal();
        $coupon = $this->getAppliedCoupon();
        if (!$coupon) {
            return 0;
        }
        if ($coupon->min_order_amount && $base < $coupon->min_order_amount) {
            return 0;
        }
        return $this->calculateDiscount($base, $coupon);
    }

    public function applyCouponByCode(string $code): ?Coupon
    {
        $now    = Carbon::now();
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            })
            ->first();

        if (!$coupon) {
            return null;
        }
        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return null;
        }

        $coupon->increment('times_used');
        Session::put('coupon', ['id' => $coupon->id, 'code' => $coupon->code]);
        $this->resolvedCoupon = $coupon;
        return $coupon;
    }

    public function removeCoupon(): void
    {
        Session::forget('coupon');
        $this->resolvedCoupon = null;
    }

    public function clear(): void
    {
        Session::forget('cart');
        Session::forget('coupon');
        $this->resolvedCoupon = null;
    }

    /**
     * Memoized coupon lookup — only one DB query per request lifecycle.
     */
    public function getAppliedCoupon(): ?Coupon
    {
        if ($this->resolvedCoupon !== null) {
            return $this->resolvedCoupon;
        }
        $c = Session::get('coupon');
        if (!$c || empty($c['id'])) {
            return null;
        }
        $this->resolvedCoupon = Coupon::find($c['id']);
        return $this->resolvedCoupon;
    }

    /**
     * Number of individual items (sum of quantities) in the cart.
     */
    public function count(): int
    {
        return (int) array_sum(array_column($this->getCart(), 'quantity'));
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function calculateDiscount(float $base, Coupon $coupon): float
    {
        if ($coupon->type === 'percent') {
            return ($coupon->value / 100) * $base;
        }
        return (float) $coupon->value;
    }
}
