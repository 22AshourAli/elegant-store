<?php

namespace App\Services;

use App\Models\AbandonedCart;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AbandonedCartService
{
    /**
     * Save or update the current cart in the abandoned_carts tracking.
     * Called during cart updates or checkout abandonment detection.
     */
    public function trackCart(?int $userId, string $sessionId, array $items, float $total, ?string $couponCode = null): AbandonedCart
    {
        $cart = AbandonedCart::recoverable()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->where('session_id', $sessionId))
            ->first();

        if (!$cart) {
            $cart = new AbandonedCart();
            $cart->recovery_token = Str::random(32);
        }

        $cart->user_id = $userId;
        $cart->session_id = $sessionId;
        $cart->items = $items;
        $cart->total = $total;
        $cart->coupon_code = $couponCode;
        $cart->save();

        return $cart;
    }

    /**
     * Mark cart as converted (order completed) after successful checkout.
     */
    public function markConverted(int $userId): void
    {
        AbandonedCart::where('user_id', $userId)
            ->recoverable()
            ->update(['status' => 'converted']);
    }

    /**
     * Find carts that have been idle beyond the threshold (default 2 hours)
     * and mark them as abandoned.
     */
    public function identifyAbandonedCarts(int $idleMinutes = 120): int
    {
        $cutoff = Carbon::now()->subMinutes($idleMinutes);

        return AbandonedCart::active()
            ->where('updated_at', '<=', $cutoff)
            ->update([
                'status' => 'abandoned',
                'first_abandoned_at' => now(),
            ]);
    }

    /**
     * Process abandoned carts for recovery notifications.
     * Generates a unique recovery coupon and prepares notification data.
     */
    public function processRecovery(int $batchSize = 50): array
    {
        $processed = [];

        $carts = AbandonedCart::abandoned()
            ->where(function ($q) {
                $q->whereNull('last_reminder_sent_at')
                    ->orWhere('reminder_count', '<', 3); // max 3 reminders
            })
            ->where('total', '>=', 100) // Only carts with meaningful value
            ->limit($batchSize)
            ->get();

        foreach ($carts as $cart) {
            $recoveryCoupon = $this->generateRecoveryCoupon($cart);

            $cart->update([
                'last_reminder_sent_at' => now(),
                'reminder_count' => $cart->reminder_count + 1,
            ]);

            $processed[] = [
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'total' => (float) $cart->total,
                'recovery_token' => $cart->recovery_token,
                'coupon_code' => $recoveryCoupon?->code,
                'reminder_count' => $cart->reminder_count,
                'recovery_url' => url("/cart/recover/{$cart->recovery_token}"),
            ];
        }

        return $processed;
    }

    /**
     * Attempt to recover a cart via its recovery token.
     * Returns the cart data so the session can be restored.
     */
    public function recoverByToken(string $token): ?AbandonedCart
    {
        $cart = AbandonedCart::where('recovery_token', $token)
            ->recoverable()
            ->first();

        if ($cart) {
            $cart->update(['status' => 'active', 'first_abandoned_at' => null]);
        }

        return $cart;
    }

    /**
     * Advanced 4: Track which checkout step the user reached before abandoning.
     */
    public function updateCheckoutStep(int $userId, string $step): void
    {
        AbandonedCart::where('user_id', $userId)
            ->recoverable()
            ->update(['checkout_step' => $step]);
    }

    private function generateRecoveryCoupon(AbandonedCart $cart): ?Coupon
    {
        $code = 'WELCOME' . strtoupper(Str::random(4));

        return Coupon::create([
            'code' => $code,
            'type' => 'percentage',
            'value' => 10, // 10% discount
            'min_order_amount' => $cart->total * 0.5,
            'valid_from' => now(),
            'valid_until' => now()->addDays(3),
            'usage_limit' => 1,
            'is_active' => true,
        ]);
    }
}
