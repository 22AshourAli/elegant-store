<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;

class OrderStatusChanged extends Notification
{
    public $order;
    public $status;
    public $forAdmin;

    public function __construct(Order $order, string $status, bool $forAdmin = false)
    {
        $this->order = $order;
        $this->status = $status;
        $this->forAdmin = $forAdmin;
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
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        $statusText = __('orders.status_' . $this->status);

        // best-effort WhatsApp notifications (customer + admin)
        try {
            $phone = $this->order->phone ?? $notifiable->phone ?? null;
            if ($phone) {
                $message = __('global.order_status_update_line', ['id' => $this->order->id, 'status' => $statusText]) . ' ' . route('orders.show', $this->order->id);
                $this->sendWhatsApp($phone, $message);
            }
            $adminNumber = config('store.admin_whatsapp', env('ADMIN_WHATSAPP', env('ADMIN_PHONE')));
            if ($adminNumber) {
                $customerName = $this->order->user->name ?? __('global.pos_guest');
                $adminMsg = __('global.admin_new_order_db', ['name' => $customerName, 'amount' => round($this->order->total), 'currency' => __('global.currency')]) . ' — ' . $statusText;
                $this->sendWhatsApp($adminNumber, $adminMsg);
            }
        } catch (\Exception $e) {
            // ignore
        }

        if ($this->forAdmin) {
            $customerName = $this->order->user->name ?? __('global.pos_guest');
            return (new MailMessage)
                ->subject(__('global.admin_new_order_subject', ['id' => $this->order->id]))
                ->greeting(__('global.greeting', ['name' => $notifiable->name]))
                ->line(__('global.admin_new_order_body', [
                    'id' => $this->order->id,
                    'name' => $customerName,
                    'amount' => round($this->order->total),
                    'currency' => __('global.currency'),
                ]) . ' — ' . $statusText)
                ->action(__('global.admin_new_order_action'), route('admin.orders.show', $this->order->id))
                ->line(__('global.admin_new_order_footer'));
        }

        return (new MailMessage)
            ->subject(__('global.order_status_update_subject', ['id' => $this->order->id]))
            ->greeting(__('global.greeting', ['name' => $notifiable->name]))
            ->line(__('global.order_status_update_line', [
                'id' => $this->order->id,
                'status' => $statusText
            ]))
            ->action(__('global.view_order'), route('orders.show', $this->order->id))
            ->line(__('global.thanks_short'));
    }

    public function toDatabase($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        $statusText = __('orders.status_' . $this->status);

        if ($this->forAdmin) {
            $customerName = $this->order->user->name ?? __('global.pos_guest');
            return [
                'order_id' => $this->order->id,
                'status' => $this->status,
                'message' => __('global.admin_new_order_db', [
                    'name' => $customerName,
                    'amount' => round($this->order->total),
                    'currency' => __('global.currency'),
                ]) . ' — ' . $statusText,
            ];
        }

        return [
            'order_id' => $this->order->id,
            'status' => $this->status,
            'message' => __('global.order_status_db', [
                'id' => $this->order->id,
                'status' => $statusText
            ]),
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
