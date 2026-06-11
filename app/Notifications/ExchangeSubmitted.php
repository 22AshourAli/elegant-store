<?php

namespace App\Notifications;

use App\Models\Exchange;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        Log::info('ExchangeSubmitted: attempting to send mail', [
            'exchange_id' => $this->exchange->id,
            'notifiable'  => $notifiable->email ?? $notifiable->id,
        ]);

        try {
            $mail = (new MailMessage)
                ->subject("طلب استبدال جديد #{$this->exchange->id}")
                ->line("تم تقديم طلب استبدال جديد من {$this->exchange->user->name}")
                ->action('عرض الطلب', route('admin.exchanges.show', $this->exchange));

            Log::info('ExchangeSubmitted: mail message built successfully', ['exchange_id' => $this->exchange->id]);

            return $mail;
        } catch (\Exception $e) {
            Log::error('ExchangeSubmitted: failed to build mail message', [
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
            'exchange_id' => $this->exchange->id,
            'order_id' => $this->exchange->order_id,
            'type' => 'exchange_submitted',
            'message' => "طلب استبدال جديد من {$this->exchange->user->name} للطلب رقم #{$this->exchange->order_id}",
        ];
    }
}
