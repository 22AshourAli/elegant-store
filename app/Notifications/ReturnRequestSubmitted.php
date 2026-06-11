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
        $type = $this->returnRequest->type === 'exchange' ? 'استبدال' : 'إرجاع';
        return (new MailMessage)
            ->subject("طلب {$type} جديد #{$this->returnRequest->id}")
            ->line("تم تقديم طلب {$type} جديد من {$this->returnRequest->user->name}")
            ->action('عرض الطلب', route('admin.returns.show', $this->returnRequest));
    }

    public function toArray($notifiable): array
    {
        $type = $this->returnRequest->type === 'exchange' ? 'exchange' : 'return';
        $typeLabel = $this->returnRequest->type === 'exchange' ? 'استبدال' : 'إرجاع';
        return [
            'return_request_id' => $this->returnRequest->id,
            'order_id' => $this->returnRequest->order_id,
            'type' => $type,
            'message' => "طلب {$typeLabel} جديد من {$this->returnRequest->user->name} للطلب رقم #{$this->returnRequest->order_id}",
        ];
    }
}
