<?php

namespace App\Notifications;

use App\Models\Exchange;
use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        Log::info('ExchangeApproved: attempting to send mail', [
            'order_id'    => $this->order->id,
            'exchange_id' => $this->exchange->id,
            'notifiable'  => $notifiable->email ?? $notifiable->id,
        ]);

        try {
            $mail = (new MailMessage)
                ->subject('تم الموافقة على طلب الاستبدال')
                ->line('تمت الموافقة على طلب الاستبدال للطلب رقم #' . $this->order->id)
                ->action('عرض التفاصيل', route('exchanges.index'));

            Log::info('ExchangeApproved: mail message built successfully', [
                'order_id'    => $this->order->id,
                'exchange_id' => $this->exchange->id,
            ]);

            return $mail;
        } catch (\Exception $e) {
            Log::error('ExchangeApproved: failed to build mail message', [
                'order_id'    => $this->order->id,
                'exchange_id' => $this->exchange->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
