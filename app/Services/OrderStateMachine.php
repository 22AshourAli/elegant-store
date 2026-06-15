<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderStateMachine
{
    const ALLOWED_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['out_for_delivery', 'cancelled'],
        'out_for_delivery' => ['delivered', 'returned'],
        'delivered' => ['returned'],
        'returned' => ['collected'],
        'cancelled' => [],
        'collected' => [],
    ];

    public function canTransition(Order $order, string $newStatus): bool
    {
        if ($order->status === $newStatus) {
            return false;
        }

        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$order->status] ?? []);
    }

    public function transition(Order $order, string $newStatus, ?string $note = null): Order
    {
        if (!$this->canTransition($order, $newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition order #{$order->id} from '{$order->status}' to '{$newStatus}'."
            );
        }

        return DB::transaction(function () use ($order, $newStatus, $note) {
            $oldStatus = $order->status;
            $order->status = $newStatus;

            if ($newStatus === 'delivered') {
                $order->delivered_at = now();
            }

            $order->save();

            activity()
                ->performedOn($order)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'note' => $note,
                ])
                ->log("Order status changed from {$oldStatus} to {$newStatus}");

            return $order;
        });
    }

    public function availableTransitions(Order $order): array
    {
        return self::ALLOWED_TRANSITIONS[$order->status] ?? [];
    }

    public static function statusLabel(string $status, string $locale = 'ar'): string
    {
        $labels = [
            'pending' => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
            'confirmed' => ['ar' => 'مؤكد', 'en' => 'Confirmed'],
            'processing' => ['ar' => 'قيد التجهيز', 'en' => 'Processing'],
            'shipped' => ['ar' => 'تم الشحن', 'en' => 'Shipped'],
            'out_for_delivery' => ['ar' => 'خرج للتوصيل', 'en' => 'Out for Delivery'],
            'delivered' => ['ar' => 'تم التوصيل', 'en' => 'Delivered'],
            'returned' => ['ar' => 'مرتجع', 'en' => 'Returned'],
            'collected' => ['ar' => 'تم التحصيل', 'en' => 'Collected'],
            'cancelled' => ['ar' => 'ملغي', 'en' => 'Cancelled'],
        ];

        return $labels[$status][$locale] ?? $status;
    }
}
