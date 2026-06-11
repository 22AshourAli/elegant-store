<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        Log::info('ReturnRequestSubmitted: attempting to send mail', [
            'return_request_id' => $this->returnRequest->id,
            'notifiable'        => $notifiable->email ?? $notifiable->id,
        ]);

        $type = $this->returnRequest->type === 'exchange' ? 'استبدال' : 'إرجاع';

        try {
            $mail = (new MailMessage)
                ->subject("طلب {$type} جديد #{$this->returnRequest->id}")
                ->line("تم تقديم طلب {$type} جديد من {$this->returnRequest->user->name}")
                ->action('عرض الطلب', route('admin.returns.show', $this->returnRequest));

            Log::info('ReturnRequestSubmitted: mail message built successfully', [
                'return_request_id' => $this->returnRequest->id,
            ]);

            return $mail;
        } catch (\Exception $e) {
            Log::error('ReturnRequestSubmitted: failed to build mail message', [
                'return_request_id' => $this->returnRequest->id,
                'error'             => $e->getMessage(),
                'trace'             => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
