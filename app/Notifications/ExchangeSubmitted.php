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
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        return (new MailMessage)
            ->subject(__('global.exchange_submitted_subject', ['id' => $this->exchange->id]))
            ->line(__('global.exchange_submitted_body', ['name' => $this->exchange->user->name]))
            ->action(__('global.view_order'), route('admin.exchanges.show', $this->exchange));
    }

    public function toArray($notifiable): array
    {
        return [
            'exchange_id' => $this->exchange->id,
            'order_id' => $this->exchange->order_id,
            'type' => 'exchange_submitted',
            'message' => __('global.exchange_submitted_body', ['name' => $this->exchange->user->name]) . ' ' . __('global.order') . ' #' . $this->exchange->order_id,
        ];
    }
}
