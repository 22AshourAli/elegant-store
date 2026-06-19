<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnRequestSubmitted extends Notification
{
    use Queueable;

    public ReturnRequest $returnRequest;

    public function __construct(ReturnRequest $returnRequest)
    {
        $this->returnRequest = $returnRequest;
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

        $type = $this->returnRequest->type === 'exchange'
            ? __('global.admin_exchange')
            : __('global.admin_return');

        return (new MailMessage)
            ->subject(__('global.return_submitted_subject', ['type' => $type, 'id' => $this->returnRequest->id]))
            ->line(__('global.return_submitted_body', ['type' => $type, 'name' => $this->returnRequest->user->name]))
            ->action(__('global.view_order'), route('admin.returns.show', $this->returnRequest));
    }

    public function toArray($notifiable): array
    {
        $type = $this->returnRequest->type === 'exchange' ? 'exchange' : 'return';
        $typeLabel = $this->returnRequest->type === 'exchange'
            ? __('global.admin_exchange')
            : __('global.admin_return');

        return [
            'return_request_id' => $this->returnRequest->id,
            'order_id' => $this->returnRequest->order_id,
            'type' => $type,
            'message' => __('global.return_submitted_body', ['type' => $typeLabel, 'name' => $this->returnRequest->user->name]) . ' ' . __('global.order') . ' #' . $this->returnRequest->order_id,
        ];
    }
}
