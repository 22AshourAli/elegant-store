<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderDeliveredNotification extends Notification
{
    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return array_values(array_filter([
            'database',
            in_array(config('mail.default'), ['log', 'array', null]) ? null : 'mail',
        ]));
    }

    public function toMail($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        $deliveredAt = $this->order->delivered_at
            ? $this->order->delivered_at->format('Y-m-d')
            : now()->format('Y-m-d');

        return (new MailMessage)
            ->subject(__('return.delivery_notification_subject', ['id' => $this->order->id]))
            ->greeting(__('مرحباً :name', ['name' => $notifiable->name]))
            ->line(__('return.delivery_notification_body', ['id' => $this->order->id, 'date' => $deliveredAt]))
            ->line(__('return.delivery_notification_window'))
            ->action(__('return.view_order'), route('orders.show', $this->order->id))
            ->line(__('return.thanks'));
    }

    public function toDatabase($notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        return [
            'order_id' => $this->order->id,
            'message' => __('return.delivery_db_notification', ['id' => $this->order->id]),
        ];
    }
}
