<?php

namespace App\Notifications;

use App\Models\Exchange;
use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExchangeApproved extends Notification
{
    public Order $order;
    public $exchange;

    public function __construct(Order $order, $exchange)
    {
        $this->order = $order;
        $this->exchange = $exchange;
    }

    public function via($notifiable): array
    {
        return array_values(array_filter([
            'database',
            in_array(config('mail.default'), ['log', 'array', null]) ? null : 'mail',
        ]));
    }

    public function toMail($notifiable): MailMessage
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        return (new MailMessage)
            ->subject(__('global.exchange_approved_subject'))
            ->line(__('global.exchange_approved_body', ['order_id' => $this->order->id]))
            ->action(__('global.view_order'), route('exchanges.index'));
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'return_request_id' => $this->exchange->id,
            'type' => 'exchange_approved',
            'message' => __('global.exchange_approved_body', ['order_id' => $this->order->id]),
        ];
    }
}
