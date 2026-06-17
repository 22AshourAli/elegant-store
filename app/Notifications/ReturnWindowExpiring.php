<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReturnWindowExpiring extends Notification
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

        $daysAgo = $this->order->delivered_at
            ? (int) $this->order->delivered_at->diffInDays(now())
            : 2;

        return (new MailMessage)
            ->subject(__('return.return_window_expiry_reminder_subject'))
            ->greeting(__('مرحباً :name', ['name' => $notifiable->name]))
            ->line(__('return.return_window_expiry_reminder_body', [
                'id' => $this->order->id,
                'days' => $daysAgo,
            ]))
            ->action(__('return.view_order'), route('orders.show', $this->order->id))
            ->line(__('return.thanks'));
    }

    public function toDatabase($notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        return [
            'order_id' => $this->order->id,
            'message' => __('return.return_window_expiry_reminder_body', [
                'id' => $this->order->id,
                'days' => $this->order->delivered_at ? (int) $this->order->delivered_at->diffInDays(now()) : 2,
            ]),
        ];
    }
}
