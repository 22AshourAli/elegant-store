<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
        return ['mail', 'database'];
    }

    private function getTypeLabel(): string
    {
        return $this->request instanceof \App\Models\Exchange ? 'الاستبدال' : 'الإرجاع';
    }

    private function getActionLabel(): string
    {
        return $this->request->status === 'approved' ? 'الموافقة' : 'الرفض';
    }

    public function toMail($notifiable): MailMessage
    {
        $type   = $this->getTypeLabel();
        $action = $this->getActionLabel();

        Log::info('ReturnRequestProcessed: attempting to send mail', [
            'request_id' => $this->request->id,
            'type'       => $type,
            'action'     => $action,
            'notifiable' => $notifiable->email ?? $notifiable->id,
        ]);

        try {
            $mail = (new MailMessage)
                ->subject("تم {$action} طلب {$type} #{$this->request->id}")
                ->line("تم {$action} على طلب {$type} للطلب رقم #{$this->request->order_id}")
                ->action('عرض التفاصيل', route('returns.index'));

            Log::info('ReturnRequestProcessed: mail message built successfully', [
                'request_id' => $this->request->id,
            ]);

            return $mail;
        } catch (\Exception $e) {
            Log::error('ReturnRequestProcessed: failed to build mail message', [
                'request_id' => $this->request->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
