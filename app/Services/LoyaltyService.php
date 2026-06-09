<?php

namespace App\Services;

use App\Models\CustomerWallet;
use App\Models\LoyaltyPointsLog;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Configuration: points earned per currency unit spent.
     * Default: 1 point per 10 EGP spent.
     */
    const POINTS_PER_CURRENCY = 10; // 1 point per 10 EGP
    const CURRENCY_PER_POINT = 10;

    /**
     * Award loyalty points when an order is completed and paid.
     */
    public function awardOrderPoints(Order $order): void
    {
        if ($order->payment_status !== 'paid' || $order->user_id === null) {
            return;
        }

        $points = $this->calculatePoints($order->total);
        if ($points <= 0) return;

        DB::transaction(function () use ($order, $points) {
            $wallet = CustomerWallet::firstOrCreate(
                ['user_id' => $order->user_id],
                ['balance' => 0, 'loyalty_points' => 0, 'lifetime_spent' => 0]
            );

            $wallet->increment('loyalty_points', $points);
            $wallet->increment('lifetime_spent', $order->total);

            LoyaltyPointsLog::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'points' => $points,
                'type' => 'earned',
                'description' => "Points earned from order #{$order->id}",
                'meta' => ['order_total' => (float) $order->total],
            ]);
        });
    }

    /**
     * Redeem points. Returns the discount amount in currency.
     * e.g. 100 points = 10 EGP discount
     */
    public function redeemPoints(int $userId, int $pointsToRedeem, int $orderId): float
    {
        $wallet = CustomerWallet::where('user_id', $userId)->firstOrFail();

        if ($wallet->loyalty_points < $pointsToRedeem) {
            throw new \RuntimeException('Insufficient points');
        }

        $discount = $pointsToRedeem / self::POINTS_PER_CURRENCY;

        DB::transaction(function () use ($wallet, $pointsToRedeem, $discount, $orderId, $userId) {
            $wallet->decrement('loyalty_points', $pointsToRedeem);

            LoyaltyPointsLog::create([
                'user_id' => $userId,
                'order_id' => $orderId,
                'points' => -$pointsToRedeem,
                'type' => 'spent',
                'description' => "Points redeemed for order #{$orderId}",
                'meta' => ['discount_amount' => $discount],
            ]);
        });

        return $discount;
    }

    /**
     * Calculate points for a given total.
     */
    public function calculatePoints(float $total): int
    {
        return (int) floor($total / self::POINTS_PER_CURRENCY);
    }

    /**
     * Get customer analytics for the profile/CRM.
     */
    public function getCustomerAnalytics(int $userId): array
    {
        $orders = Order::where('user_id', $userId)
            ->with('items')
            ->where('payment_status', 'paid')
            ->get();

        $allItems = $orders->flatMap->items;

        // Favorite sizes
        $sizeCounts = $allItems->groupBy('size')->map->count()->sortDesc();

        // Favorite colors
        $colorCounts = $allItems->groupBy('color')->map->count()->sortDesc();

        // Monthly spending trend
        $monthlySpending = $orders
            ->groupBy(fn($o) => $o->created_at->format('Y-m'))
            ->map(fn($group) => (float) $group->sum('total'));

        $wallet = CustomerWallet::where('user_id', $userId)->first();

        return [
            'orders_count' => $orders->count(),
            'lifetime_spent' => (float) ($wallet?->lifetime_spent ?? $orders->sum('total')),
            'loyalty_points' => (int) ($wallet?->loyalty_points ?? 0),
            'favorite_sizes' => $sizeCounts->take(3)->keys()->toArray(),
            'favorite_colors' => $colorCounts->take(3)->keys()->toArray(),
            'monthly_spending' => $monthlySpending,
            'last_order_date' => $orders->first()?->created_at?->format('Y-m-d'),
        ];
    }
}
