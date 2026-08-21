<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Finite state machine governing order status transitions.
 *
 * Enforces a strict lifecycle:
 *   Pending → Confirmed → Processing → Shipped → OutForDelivery → Delivered → Returned → Collected
 *                        ↘ Cancelled (at any stage before Delivered)
 *
 * Every transition is logged to the activity_logs table for audit trail.
 */
class OrderStateMachine
{
    /**
     * Allowed transitions from each status.
     * Key = current status, Value = array of valid next statuses.
     */
    const ALLOWED_TRANSITIONS = [
        OrderStatus::Pending->value       => [OrderStatus::Confirmed->value, OrderStatus::Cancelled->value],
        OrderStatus::Confirmed->value     => [OrderStatus::Processing->value, OrderStatus::Cancelled->value],
        OrderStatus::Processing->value    => [OrderStatus::Shipped->value, OrderStatus::Cancelled->value],
        OrderStatus::Shipped->value       => [OrderStatus::OutForDelivery->value, OrderStatus::Cancelled->value],
        OrderStatus::OutForDelivery->value => [OrderStatus::Delivered->value, OrderStatus::Returned->value],
        OrderStatus::Delivered->value     => [OrderStatus::Returned->value],
        OrderStatus::Returned->value      => [OrderStatus::Collected->value],
        OrderStatus::Cancelled->value     => [],
        OrderStatus::Collected->value     => [],
    ];

    /**
     * Check if a transition from the order's current status to $newStatus is valid.
     */
    public function canTransition(Order $order, string $newStatus): bool
    {
        if ($order->status === $newStatus) {
            return false;
        }

        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$order->status] ?? []);
    }

    /**
     * Execute the status transition inside a DB transaction.
     *
     * - Updates the order status.
     * - Sets delivered_at timestamp when transitioning to 'delivered'.
     * - Logs the transition to activity_logs for audit purposes.
     *
     * @throws \InvalidArgumentException If the transition is not allowed.
     */
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

            // Log the status change to the activity_logs table for audit trail
            DB::table('activity_logs')->insert([
                'user_id'     => auth()->id(),
                'action'      => 'status_changed',
                'module'      => 'orders',
                'subject_id'  => $order->id,
                'subject_type' => Order::class,
                'old_values'  => json_encode(['status' => $oldStatus]),
                'new_values'  => json_encode(['status' => $newStatus, 'note' => $note]),
                'ip_address'  => request()->ip(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return $order;
        });
    }

    /**
     * Get the list of statuses the order can transition to from its current state.
     */
    public function availableTransitions(Order $order): array
    {
        return self::ALLOWED_TRANSITIONS[$order->status] ?? [];
    }

    /**
     * Get a human-readable label for a given status.
     *
     * @param string $status  The order status value.
     * @param string $locale  Language code ('ar' or 'en').
     */
    public static function statusLabel(string $status, string $locale = 'ar'): string
    {
        $labels = [
            OrderStatus::Pending->value       => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
            OrderStatus::Confirmed->value     => ['ar' => 'مؤكد', 'en' => 'Confirmed'],
            OrderStatus::Processing->value    => ['ar' => 'قيد التجهيز', 'en' => 'Processing'],
            OrderStatus::Shipped->value       => ['ar' => 'تم الشحن', 'en' => 'Shipped'],
            OrderStatus::OutForDelivery->value => ['ar' => 'خرج للتوصيل', 'en' => 'Out for Delivery'],
            OrderStatus::Delivered->value     => ['ar' => 'تم التوصيل', 'en' => 'Delivered'],
            OrderStatus::Returned->value      => ['ar' => 'مرتجع', 'en' => 'Returned'],
            OrderStatus::Collected->value     => ['ar' => 'تم التحصيل', 'en' => 'Collected'],
            OrderStatus::Cancelled->value     => ['ar' => 'ملغي', 'en' => 'Cancelled'],
        ];

        return $labels[$status][$locale] ?? $status;
    }
}
