<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\UserCart;
use App\Services\AbandonedCartService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

/**
 * Shopping cart management service.
 *
 * Handles all cart operations: add/remove/update items, coupon management,
 * cart persistence to database for logged-in users, and session synchronization.
 * The session is the primary cart store; DB persistence provides cross-device sync.
 */
class CartService
{
    /** @var Coupon|null Resolved coupon instance to avoid repeated DB queries */
    private ?Coupon $resolvedCoupon = null;

    /** Get raw cart items from session (variant_id => ['variant_id', 'quantity']). */
    public function getCart(): array
    {
        return Session::get('cart', []);
    }

    /** Add a variant to the cart (increments quantity if already present). */
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

    /** Remove a variant from the cart entirely. */
    public function remove(int $variantId): void
    {
        $cart = $this->getCart();
        unset($cart[$variantId]);
        Session::put('cart', $cart);
        $this->persistToDb();
    }

    /** Update quantity for a specific cart item; removes if quantity <= 0. */
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
     * Enrich raw cart data with full product/variant details from DB.
     *
     * Automatically removes trashed/inactive variants and soft-deleted products.
     * Returns an array keyed by variant_id with product_name, price, image, etc.
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
                $this->remove($variantId);
                continue;
            }
            $variant = $variants[$variantId];

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

    /** Sum of (price × quantity) for all items before coupon discount. */
    public function baseTotal(): float
    {
        return array_sum(
            array_map(fn($i) => $i['price'] * $i['quantity'], $this->getEnrichedCart())
        );
    }

    /** Cart total after applying coupon discount (floor: 0). */
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

    /** Calculate the coupon discount amount to subtract from the cart total. */
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

    /**
     * Apply a coupon code to the cart.
     *
     * Validates: active status, validity window, usage limit.
     * Increments usage count on success. Returns null if invalid.
     */
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

    /** Remove applied coupon from session and resolve cache. */
    public function removeCoupon(): void
    {
        Session::forget('coupon');
        $this->resolvedCoupon = null;
        $this->persistToDb();
    }

    /** Clear entire cart and coupon from session and DB. */
    public function clear(): void
    {
        Session::forget('cart');
        Session::forget('coupon');
        $this->resolvedCoupon = null;
        $this->persistToDb();
    }

    /** Retrieve the currently applied coupon (lazy-loaded from session). */
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

    public function count(): int
    {
        return (int) array_sum(array_column($this->getCart(), 'quantity'));
    }

    /**
     * Overwrite the DB cart with the current session data.
     * No merge — the session is always the latest state.
     */
    public function persistToDb(): void
    {
        $userId = auth()->id();
        if (!$userId) return;

        $sessionItems = $this->getCart();
        $coupon = Session::get('coupon');

        UserCart::updateOrCreate(
            ['user_id' => $userId],
            [
                'items'       => $sessionItems,
                'coupon_code' => $coupon['code'] ?? null,
            ]
        );

        $this->trackAbandonedCart();
    }

    private function trackAbandonedCart(): void
    {
        $userId = auth()->id();
        if (!$userId) return;

        $items = $this->getCart();
        if (empty($items)) return;

        $coupon = Session::get('coupon');

        app(AbandonedCartService::class)->trackCart(
            $userId,
            Session::getId(),
            $items,
            $this->baseTotal(),
            $coupon['code'] ?? null
        );
    }

    /**
     * Load the DB cart into the session (DB is source of truth).
     *
     * - If DB is empty AND session has items → stale session from another device
     *   that already purchased → clear the session.
     * - If DB has items → overwrite the session with DB data.
     */
    public function syncFromDb(): void
    {
        $userId = auth()->id();
        if (!$userId) return;

        $saved = UserCart::forUser($userId);

        $sessionCart = $this->getCart();

        // Case 1: DB is empty but session still has items → stale session
        if (!$saved || empty($saved->items)) {
            if (!empty($sessionCart)) {
                Session::forget('cart');
                Session::forget('coupon');
                $this->resolvedCoupon = null;
            }
            return;
        }

        // Case 2: DB has items → DB is source of truth, overwrite session
        Session::put('cart', $saved->items);

        if ($saved->coupon_code && !Session::has('coupon')) {
            $coupon = Coupon::where('code', $saved->coupon_code)
                ->where('is_active', true)->first();
            if ($coupon) {
                Session::put('coupon', ['id' => $coupon->id, 'code' => $coupon->code]);
                $this->resolvedCoupon = $coupon;
            }
        }
    }

    private function calculateDiscount(float $base, Coupon $coupon): float
    {
        if ($coupon->type === 'percentage') {
            return ($coupon->value / 100) * $base;
        }
        return (float) $coupon->value;
    }
}
