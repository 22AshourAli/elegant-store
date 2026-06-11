<?php

namespace App\Notifications;

use App\Models\Exchange;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExchangeSubmitted extends Notification
{
    use Queueable;

    public Exchange $exchange;

    public function __construct(Exchange $exchange)
    {
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
        return (new MailMessage)
            ->subject("طلب استبدال جديد #{$this->exchange->id}")
            ->line("تم تقديم طلب استبدال جديد من {$this->exchange->user->name}")
            ->action('عرض الطلب', route('admin.exchanges.show', $this->exchange));
    }

    public function toArray($notifiable): array
    {
        return [
            'exchange_id' => $this->exchange->id,
            'order_id' => $this->exchange->order_id,
            'type' => 'exchange_submitted',
            'message' => "طلب استبدال جديد من {$this->exchange->user->name} للطلب رقم #{$this->exchange->order_id}",
        ];
    }
}
