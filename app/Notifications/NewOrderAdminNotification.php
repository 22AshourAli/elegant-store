<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;

class NewOrderAdminNotification extends Notification
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return array_values(array_filter([
            'database',
            in_array(config('mail.default'), ['log', 'array', null]) ? null : 'mail',
        ]));
    }

    public function toMail($notifiable)
    {
        // Attempt to send a WhatsApp alert to the admin (best-effort) if Twilio is configured
        try {
            $adminNumber = config('store.admin_whatsapp', env('ADMIN_WHATSAPP', env('ADMIN_PHONE')));
            $message = "طلب جديد رقم #{$this->order->id} من {$this->order->user->name} بقيمة {$this->order->total} ج.م. " . url(route('admin.orders.show', $this->order->id));
            if ($adminNumber) {
                $this->sendWhatsApp($adminNumber, $message);
            }
        } catch (\Exception $e) {
            // ignore errors; mail will still be sent
        }

        return (new MailMessage)
            ->subject(__('إشعار: طلب جديد #:id', ['id' => $this->order->id]))
            ->greeting(__('مرحباً'))
            ->line(__('تم استلام طلب جديد رقم #:id من :name بقيمة :amount ج.م.', ['id' => $this->order->id, 'name' => $this->order->user->name, 'amount' => round($this->order->total)]))
            ->action(__('عرض تفاصيل الطلب'), route('admin.orders.show', $this->order->id))
            ->line(__('الرجاء مراجعة الطلب في لوحة التحكم.'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message' => __('طلب جديد من :name بقيمة :amount ج.م.', ['name' => $this->order->user->name, 'amount' => round($this->order->total)]),
        ];
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
