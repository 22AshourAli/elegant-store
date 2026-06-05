<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;

class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;
    public $status;

    public function __construct(Order $order, string $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        $statusText = __('orders.status_' . $this->status);

        // best-effort WhatsApp notifications (customer + admin)
        try {
            $phone = $this->order->phone ?? $notifiable->phone ?? null;
            $statusText = __('orders.status_' . $this->status);
            $message = "تم تحديث حالة طلبك #{$this->order->id} إلى {$statusText}. تفاصيل: " . route('orders.show', $this->order->id);
            if ($phone) $this->sendWhatsApp($phone, $message);
            $adminNumber = config('store.admin_whatsapp', env('ADMIN_WHATSAPP', env('ADMIN_PHONE')));
            if ($adminNumber) $this->sendWhatsApp($adminNumber, "تم تحديث حالة الطلب #{$this->order->id} إلى {$statusText}.");
        } catch (\Exception $e) {
            // ignore
        }

        return (new MailMessage)
            ->subject(__('تحديث حالة طلبك #:id', ['id' => $this->order->id]))
            ->greeting(__('مرحباً :name', ['name' => $notifiable->name]))
            ->line(__('تم تحديث حالة طلبك #:id إلى :status', [
                'id' => $this->order->id,
                'status' => $statusText
            ]))
            ->action(__('عرض الطلب'), route('orders.show', $this->order->id))
            ->line(__('شكراً لتسوقك معنا في Elegant Store!'));
    }

    public function toDatabase($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        return [
            'order_id' => $this->order->id,
            'status' => $this->status,
            'message' => __('طلبك #:id أصبح :status', [
                'id' => $this->order->id,
                'status' => __('orders.status_' . $this->status)
            ]),
        ];
    }

    public function afterSend($notifiable)
    {
        // send WhatsApp to customer if phone available
        try {
            $phone = $this->order->phone ?? $notifiable->phone ?? null;
            $statusText = __('orders.status_' . $this->status);
            $message = "تم تحديث حالة طلبك #{$this->order->id} إلى {$statusText}. تفاصيل: " . route('orders.show', $this->order->id);
            if ($phone) {
                $this->sendWhatsApp($phone, $message);
            }

            // also notify admin via WhatsApp
            $adminNumber = config('store.admin_whatsapp', env('ADMIN_WHATSAPP', env('ADMIN_PHONE')));
            if ($adminNumber) {
                $adminMsg = "تم تحديث حالة الطلب #{$this->order->id} إلى {$statusText}.";
                $this->sendWhatsApp($adminNumber, $adminMsg);
            }
        } catch (\Exception $e) {
            // ignore
        }
    }

    private function sendWhatsApp($to, $message)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_WHATSAPP_FROM');
        if (!$sid || !$token || !$from) return false;

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $response = Http::withBasicAuth($sid, $token)->asForm()->post($url, [
            'From' => 'whatsapp:' . $from,
            'To' => 'whatsapp:' . $to,
            'Body' => $message,
        ]);

        return $response->successful();
    }
}
