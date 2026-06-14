<?php

namespace App\Notifications;

use App\Models\AbandonedCart;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AbandonedCartReminder extends Notification
{
    public function __construct(
        public AbandonedCart $cart,
        public string $recoveryUrl,
        public ?string $couponCode = null,
        public int $reminderCount = 1,
    ) {}

    public function via($notifiable): array
    {
        return array_values(array_filter([
            'database',
            in_array(config('mail.default'), ['log', 'array', null]) ? null : 'mail',
        ]));
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('سلة التسوق في انتظارك!'))
            ->greeting(__('مرحباً :name', ['name' => $notifiable->name ?? '']))
            ->line(__('تذكرنا أنك تركت بعض المنتجات في سلة التسوق.'))
            ->line(__('قيمة مشترياتك: :total EGP', ['total' => number_format($this->cart->total, 2)]));

        if ($this->couponCode) {
            $mail->line(__('استخدم كود الخصم :code للحصول على خصم 10%!', ['code' => $this->couponCode]))
                ->line(__('الكود صالح لمدة 3 أيام فقط.'));
        }

        $mail->action(__('العودة إلى السلة'), $this->recoveryUrl)
            ->line(__('لا تترك منتجاتك المفضلة، عد وأكمل طلبك!'));

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'cart_id' => $this->cart->id,
            'total' => (float) $this->cart->total,
            'coupon_code' => $this->couponCode,
            'reminder_count' => $this->reminderCount,
            'recovery_url' => $this->recoveryUrl,
            'message' => __('سلة التسوق في انتظارك بقيمة :total EGP' . ($this->couponCode ? ' — كود خصم :code' : ''), [
                'total' => number_format($this->cart->total, 2),
                'code' => $this->couponCode ?? '',
            ]),
        ];
    }
}
