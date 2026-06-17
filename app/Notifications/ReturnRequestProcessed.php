<?php

namespace App\Notifications;

use App\Enums\ReturnRequestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnRequestProcessed extends Notification
{
    use Queueable;

    public $request; // ReturnRequest or Exchange model

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
        return $this->request instanceof \App\Models\Exchange ? 'الاستبدال' : 'الإرجاع';
    }

    private function getActionLabel(): string
    {
        return $this->request->status === ReturnRequestStatus::Approved->value ? 'الموافقة' : 'الرفض';
    }

    public function toMail($notifiable): MailMessage
    {
        $type = $this->getTypeLabel();
        $action = $this->getActionLabel();
        return (new MailMessage)
            ->subject("تم {$action} طلب {$type} #{$this->request->id}")
            ->line("تم {$action} على طلب {$type} للطلب رقم #{$this->request->order_id}")
            ->action('عرض التفاصيل', route('returns.index'));
    }

    public function toArray($notifiable): array
    {
        $type = $this->getTypeLabel();
        $action = $this->getActionLabel();
        return [
            'return_request_id' => $this->request->id,
            'order_id' => $this->request->order_id,
            'type' => $this->request instanceof \App\Models\Exchange ? 'exchange' : 'return',
            'message' => "تم {$action} على طلب {$type} للطلب رقم #{$this->request->order_id}",
        ];
    }
}
