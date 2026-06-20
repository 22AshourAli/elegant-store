<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewReviewAdminNotification extends Notification
{
    public function __construct(
        protected Review $review
    ) {}

    public function via($notifiable)
    {
        return array_values(array_filter([
            'database',
            in_array(config('mail.default'), ['log', 'array', null]) ? null : 'mail',
        ]));
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('global.admin_new_review_subject', ['product' => $this->review->product->name]))
            ->greeting(__('global.greeting_admin'))
            ->line(__('global.admin_new_review_body', [
                'name' => $this->review->user->name,
                'product' => $this->review->product->name,
                'rating' => $this->review->rating,
            ]))
            ->line(__('global.admin_new_review_comment', ['comment' => $this->review->comment ?? __('global.no_comment')]))
            ->action(__('global.admin_new_review_action'), route('admin.reviews.show', $this->review->id));
    }

    public function toDatabase($notifiable)
    {
        return [
            'review_id' => $this->review->id,
            'product_id' => $this->review->product_id,
            'message' => __('global.admin_new_review_db', [
                'name' => $this->review->user->name,
                'product' => $this->review->product->name,
            ]),
        ];
    }
}
