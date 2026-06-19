<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Support\Facades\Request;

class OrderObserver
{
    private function log(Order $order, string $action, ?string $description = null, ?array $old = null, ?array $new = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => 'order',
            'subject_type' => Order::class,
            'subject_id' => $order->id,
            'description' => $description ?? __('global.activity_order_' . $action, ['id' => $order->id]),
            'ip_address' => Request::ip(),
        ]);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $old = $order->getOriginal('status');
            $new = $order->getAttribute('status');
            $this->log($order, 'status_changed', __('global.activity_order_status_changed', [
                'id' => $order->id,
                'old' => __("global.orders.status_{$old}"),
                'new' => __("global.orders.status_{$new}"),
            ]), ['status' => $old], ['status' => $new]);
        }
    }
}
