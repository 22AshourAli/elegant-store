<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoyaltyPointsExpiring extends Notification
{
    public function __construct(
        public int $points,
        public int $daysLeft,
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
        return (new MailMessage)
            ->subject(__('نقاط الولاء على وشك الانتهاء!'))
            ->greeting(__('مرحباً :name', ['name' => $notifiable->name ?? '']))
            ->line(__('لديك :points نقطة ولاء ستنتهي صلاحيتها بعد :days أيام.', [
                'points' => $this->points,
                'days' => $this->daysLeft,
            ]))
            ->line(__('لا تفوّت فرصة استخدام نقاطك! قم بتسوق الآن واستخدم النقاط للحصول على خصومات.'));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'points' => $this->points,
            'days_left' => $this->daysLeft,
            'message' => __('نقاط ولاء: :points نقطة ستنتهي بعد :days أيام', [
                'points' => $this->points,
                'days' => $this->daysLeft,
            ]),
        ];
    }
}
