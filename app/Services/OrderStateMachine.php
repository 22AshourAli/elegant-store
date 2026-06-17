<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderStateMachine
{
    const ALLOWED_TRANSITIONS = [
        OrderStatus::Pending->value => [OrderStatus::Confirmed->value, OrderStatus::Cancelled->value],
        OrderStatus::Confirmed->value => [OrderStatus::Processing->value, OrderStatus::Cancelled->value],
        OrderStatus::Processing->value => [OrderStatus::Shipped->value, OrderStatus::Cancelled->value],
        OrderStatus::Shipped->value => [OrderStatus::OutForDelivery->value, OrderStatus::Cancelled->value],
        OrderStatus::OutForDelivery->value => [OrderStatus::Delivered->value, OrderStatus::Returned->value],
        OrderStatus::Delivered->value => [OrderStatus::Returned->value],
        OrderStatus::Returned->value => [OrderStatus::Collected->value],
        OrderStatus::Cancelled->value => [],
        OrderStatus::Collected->value => [],
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

            if ($newStatus === OrderStatus::Delivered->value) {
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
            OrderStatus::Pending->value => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
            OrderStatus::Confirmed->value => ['ar' => 'مؤكد', 'en' => 'Confirmed'],
            OrderStatus::Processing->value => ['ar' => 'قيد التجهيز', 'en' => 'Processing'],
            OrderStatus::Shipped->value => ['ar' => 'تم الشحن', 'en' => 'Shipped'],
            OrderStatus::OutForDelivery->value => ['ar' => 'خرج للتوصيل', 'en' => 'Out for Delivery'],
            OrderStatus::Delivered->value => ['ar' => 'تم التوصيل', 'en' => 'Delivered'],
            OrderStatus::Returned->value => ['ar' => 'مرتجع', 'en' => 'Returned'],
            OrderStatus::Collected->value => ['ar' => 'تم التحصيل', 'en' => 'Collected'],
            OrderStatus::Cancelled->value => ['ar' => 'ملغي', 'en' => 'Cancelled'],
        ];

        return $labels[$status][$locale] ?? $status;
    }
}
