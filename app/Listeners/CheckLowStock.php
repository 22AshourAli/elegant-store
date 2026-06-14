<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\LowStockNotification;

class CheckLowStock
{
    public function handle(StockUpdated $event): void
    {
        $threshold = config('store.low_stock_threshold', 1);

        if ($event->stockAfter > $threshold || $event->stockBefore <= $threshold) {
            return;
        }

        $variant = ProductVariant::with('product')->find($event->variantId);
        if (!$variant || !$variant->product) {
            return;
        }

        $admins = User::whereIn('role', ['super_admin', 'manager'])->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new LowStockNotification(
                    variant: $variant,
                    stockBefore: $event->stockBefore,
                    stockAfter: $event->stockAfter,
                ));
            } catch (\Throwable $e) {
                \Log::error('LowStockNotification failed for ' . $admin->email . ': ' . $e->getMessage());
            }
        }
    }
}
