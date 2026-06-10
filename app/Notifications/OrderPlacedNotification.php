<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderPlacedNotification extends Notification
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return array_values(array_filter([
            config('mail.default') !== 'log' && !empty(config('mail.mailers.smtp.host')) && config('mail.mailers.smtp.host') !== '127.0.0.1' ? 'mail' : null,
            'database',
        ]));
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('global.order_confirmed_subject', ['id' => $this->order->id]))
            ->greeting(__('global.order_confirmed_greeting', ['name' => $notifiable->name]))
            ->line(__('global.order_confirmed_line', ['id' => $this->order->id]))
            ->line(__('global.order_products') . ':')
            ->line($this->order->items->map(fn($i) => "{$i->product_name} × {$i->quantity} = " . (int)round($i->total) . " " . __('global.currency'))->implode("\n"))
            ->line(__('global.order_total_label') . ': ' . (int)round($this->order->total) . ' ' . __('global.currency'))
            ->action(__('global.view_order'), route('orders.show', $this->order->id))
            ->line(__('global.order_thanks'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'title' => __('global.new_order_notification_title', ['id' => $this->order->id]),
            'message' => __('global.new_order_notification_msg', ['id' => $this->order->id, 'total' => (int)round($this->order->total)]),
        ];
    }
}
