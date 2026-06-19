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
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        $currency = __('global.currency');

        $mail = (new MailMessage)
            ->subject(__('global.abandoned_cart_subject'))
            ->greeting(__('global.greeting', ['name' => $notifiable->name ?? '']))
            ->line(__('global.abandoned_cart_line1'))
            ->line(__('global.abandoned_cart_total', ['total' => number_format($this->cart->total, 2), 'currency' => $currency]));

        if ($this->couponCode) {
            $mail->line(__('global.abandoned_cart_coupon', ['code' => $this->couponCode]))
                ->line(__('global.abandoned_cart_coupon_valid'));
        }

        $mail->action(__('global.abandoned_cart_action'), $this->recoveryUrl)
            ->line(__('global.abandoned_cart_footer'));

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        app()->setLocale($locale);

        $currency = __('global.currency');
        $couponNote = $this->couponCode ? ' — ' . __('global.coupon_discount_label') . ': ' . $this->couponCode : '';

        return [
            'cart_id' => $this->cart->id,
            'total' => (float) $this->cart->total,
            'coupon_code' => $this->couponCode,
            'reminder_count' => $this->reminderCount,
            'recovery_url' => $this->recoveryUrl,
            'message' => __('global.abandoned_cart_db_message', [
                'total' => number_format($this->cart->total, 2),
                'currency' => $currency,
                'coupon_note' => $couponNote,
            ]),
        ];
    }
}
