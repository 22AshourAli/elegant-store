<?php

namespace App\Notifications;

use App\Enums\ReturnRequestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnRequestProcessed extends Notification
{
    use Queueable;

    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function via($notifiable): array
    {
        return array_values(array_filter([
            'database',
            in_array(config('mail.default'), ['log', 'array', null]) ? null : 'mail',
        ]));
    }

    private function getTypeLabel(): string
    {
        return $this->request instanceof \App\Models\Exchange
            ? __('global.admin_exchange')
            : __('global.admin_return');
    }

    private function getActionLabel(): string
    {
        return $this->request->status === ReturnRequestStatus::Approved->value
            ? __('global.admin_approve_return')
            : __('global.admin_reject_request');
    }

    public function toMail($notifiable): MailMessage
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        $type = $this->getTypeLabel();
        $action = $this->getActionLabel();
        return (new MailMessage)
            ->subject(__('global.return_processed_subject', ['action' => $action, 'type' => $type, 'id' => $this->request->id]))
            ->line(__('global.return_processed_body', ['action' => $action, 'type' => $type, 'order_id' => $this->request->order_id]))
            ->action(__('global.view_order'), route('returns.index'));
    }

    public function toArray($notifiable): array
    {
        $type = $this->getTypeLabel();
        $action = $this->getActionLabel();
        return [
            'return_request_id' => $this->request->id,
            'order_id' => $this->request->order_id,
            'type' => $this->request instanceof \App\Models\Exchange ? 'exchange' : 'return',
            'message' => __('global.return_processed_body', ['action' => $action, 'type' => $type, 'order_id' => $this->request->order_id]),
        ];
    }
}
