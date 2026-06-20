<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\UserCart;
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
        $this->persistToDb();
        return $this->getEnrichedCart();
    }

    public function remove(int $variantId): void
    {
        $cart = $this->getCart();
        unset($cart[$variantId]);
        Session::put('cart', $cart);
        $this->persistToDb();
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
            $this->persistToDb();
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
        $variants   = ProductVariant::with('product', 'product.media', 'product.variants', 'media')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $enriched = [];
        foreach ($raw as $variantId => $item) {
            if (!isset($variants[$variantId])) {
                // Variant was hard-deleted; remove it silently.
                $this->remove($variantId);
                continue;
            }
            $variant = $variants[$variantId];

            // If the parent product was soft-deleted, purge this item from the cart silently.
            if ($variant->product === null || $variant->trashed() || ($variant->product->deleted_at !== null)) {
                $this->remove($variantId);
                continue;
            }

            $image = $variant->imageUrl();
            if (!$image && $variant->color) {
                $sibling = $variant->product->variants
                    ->where('color', $variant->color)
                    ->first(fn($v) => $v->image_url || $v->hasMedia('variant_images'));
                $image = $sibling ? $sibling->imageUrl() : null;
            }
            $enriched[$variantId] = [
                'variant_id'   => $variantId,
                'product_name' => $variant->product->name,
                'color'        => $variant->color,
                'size'         => $variant->size,
                'price'        => $variant->current_price,
                'image'        => $image ?: $variant->product->firstImageUrl(),
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
        $this->persistToDb();
        return $coupon;
    }

    public function removeCoupon(): void
    {
        Session::forget('coupon');
        $this->resolvedCoupon = null;
        $this->persistToDb();
    }

    public function clear(): void
    {
        Session::forget('cart');
        Session::forget('coupon');
        $this->resolvedCoupon = null;
        // Directly clear DB record — bypass merge loop in persistToDb()
        $userId = $this->userId();
        if ($userId) {
            UserCart::updateOrCreate(
                ['user_id' => $userId],
                ['items' => [], 'coupon_code' => null]
            );
        }
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

    // -------------------------------------------------------------------------
    // Cross-device cart sync
    // -------------------------------------------------------------------------

    private function userId(): ?int
    {
        return auth()->check() ? auth()->id() : null;
    }

    /**
     * Merge a DB-stored cart into the current session cart (guest wins on conflict).
     */
    public function syncFromDb(?int $userId = null): void
    {
        $userId ??= $this->userId();
        if (!$userId) return;

        $saved = UserCart::forUser($userId);
        if (!$saved) return;

        $sessionCart = $this->getCart();
        $dbCart = $saved->items ?? [];

        // Merge: session items keep their quantity, DB items fill in gaps
        foreach ($dbCart as $variantId => $item) {
            if (!isset($sessionCart[$variantId])) {
                $sessionCart[$variantId] = $item;
            }
        }

        Session::put('cart', $sessionCart);

        if ($saved->coupon_code && !Session::has('coupon')) {
            $coupon = Coupon::where('code', $saved->coupon_code)
                ->where('is_active', true)->first();
            if ($coupon) {
                Session::put('coupon', ['id' => $coupon->id, 'code' => $coupon->code]);
                $this->resolvedCoupon = $coupon;
            }
        }
    }

    /**
     * Persist the current session cart + coupon to the database for cross-device sync.
     * Merges with existing DB items (DB items fill gaps not in the current session)
     * so simultaneous saves from multiple devices don't lose items.
     */
    public function persistToDb(?int $userId = null): void
    {
        $userId ??= $this->userId();
        if (!$userId) return;

        $saved = UserCart::forUser($userId);
        $existingItems = $saved ? ($saved->items ?? []) : [];

        $sessionItems = $this->getCart();

        // Merge: DB items fill gaps that the current session doesn't have.
        // This prevents data loss when two devices persist at nearly the same time.
        foreach ($existingItems as $variantId => $item) {
            if (!isset($sessionItems[$variantId])) {
                $sessionItems[$variantId] = $item;
            }
        }

        $coupon = Session::get('coupon');

        UserCart::updateOrCreate(
            ['user_id' => $userId],
            [
                'items'       => empty($sessionItems) ? [] : $sessionItems,
                'coupon_code' => $coupon['code'] ?? null,
            ]
        );
    }

    private function calculateDiscount(float $base, Coupon $coupon): float
    {
        if ($coupon->type === 'percent') {
            return ($coupon->value / 100) * $base;
        }
        return (float) $coupon->value;
    }
}
