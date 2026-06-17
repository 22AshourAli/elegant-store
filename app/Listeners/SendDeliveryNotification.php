<?php

namespace App\Listeners;

use App\Events\OrderDelivered;
use App\Notifications\OrderDeliveredNotification;

class SendDeliveryNotification
{
    public function handle(OrderDelivered $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if ($user) {
            $user->notify(new OrderDeliveredNotification($order));
        }
    }
}
