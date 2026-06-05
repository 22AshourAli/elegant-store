<?php

namespace App\Notifications;

use App\Models\Exchange;
use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExchangeApproved extends Notification
{
    public Order $order;
    public $exchange; // Exchange or ReturnRequest model

    public function __construct(Order $order, $exchange)
    {
        $this->order = $order;
        $this->exchange = $exchange;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تم الموافقة على طلب الاستبدال')
            ->line('تمت الموافقة على طلب الاستبدال للطلب رقم #' . $this->order->id)
            ->action('عرض التفاصيل', route('exchanges.index'));
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'return_request_id' => $this->exchange->id,
            'type' => 'exchange_approved',
            'message' => 'تمت الموافقة على طلب الاستبدال للطلب رقم #' . $this->order->id,
        ];
    }
}
