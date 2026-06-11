<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class OrderPlacedNotification extends Notification
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        Log::info('OrderPlacedNotification: attempting to send mail', [
            'order_id'   => $this->order->id,
            'notifiable' => $notifiable->email ?? $notifiable->id,
        ]);

        try {
            $mail = (new MailMessage)
                ->subject(__('global.order_confirmed_subject', ['id' => $this->order->id]))
                ->greeting(__('global.order_confirmed_greeting', ['name' => $notifiable->name]))
                ->line(__('global.order_confirmed_line', ['id' => $this->order->id]))
                ->line(__('global.order_products') . ':')
                ->line($this->order->items->map(fn($i) => "{$i->product_name} × {$i->quantity} = " . (int)round($i->total) . " " . __('global.currency'))->implode("\n"))
                ->line(__('global.order_total_label') . ': ' . (int)round($this->order->total) . ' ' . __('global.currency'))
                ->action(__('global.view_order'), route('orders.show', $this->order->id))
                ->line(__('global.order_thanks'));

            Log::info('OrderPlacedNotification: mail message built successfully', ['order_id' => $this->order->id]);

            return $mail;
        } catch (\Exception $e) {
            Log::error('OrderPlacedNotification: failed to build mail message', [
                'order_id' => $this->order->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
