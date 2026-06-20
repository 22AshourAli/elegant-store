<?php

namespace App\Listeners;

use App\Events\NewAdminNotification;
use App\Models\User;
use Illuminate\Notifications\Events\NotificationSent;

class BroadcastAdminNotification
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database') {
            return;
        }

        $notifiable = $event->notifiable;
        if (! $notifiable instanceof User) {
            return;
        }

        if (! in_array($notifiable->role, ['super_admin', 'manager'], true)) {
            return;
        }

        $notifData = method_exists($event->notification, 'toDatabase')
            ? $event->notification->toDatabase($notifiable)
            : [];

        $type = $this->resolveType($notifData);
        $url = $this->resolveUrl($notifData, $type);

        $unread = $notifiable->unreadNotifications()->count();
        $latest = $notifiable->notifications()->latest()->first();

        NewAdminNotification::dispatch([
            'count'               => $unread,
            'latest_id'           => $latest?->id,
            'latest_created_at'   => $latest?->created_at?->toIso8601String(),
            'notification'        => [
                'id'      => $latest?->id,
                'title'   => $notifData['message'] ?? ($notifData['title'] ?? ''),
                'time'    => $latest?->created_at?->diffForHumans(),
                'read_at' => null,
                'type'    => $type,
                'url'     => $url,
            ],
        ]);
    }

    private function resolveType(array $data): string
    {
        $type = $data['type'] ?? null;
        if (in_array($type, ['exchange', 'exchange_approved', 'exchange_submitted'])) {
            return 'exchange';
        }
        if ($type === 'return') {
            return 'return';
        }
        if (isset($data['review_id'])) {
            return 'review';
        }
        if (isset($data['order_id'])) {
            return 'order';
        }
        return 'info';
    }

    private function resolveUrl(array $data, string $type): ?string
    {
        return match ($type) {
            'exchange' => isset($data['exchange_id'])
                ? route('admin.exchanges.show', $data['exchange_id'])
                : (isset($data['return_request_id'])
                    ? route('admin.exchanges.show', $data['return_request_id'])
                    : null),
            'return' => isset($data['return_request_id'])
                ? route('admin.returns.show', $data['return_request_id'])
                : null,
            'review' => isset($data['review_id'])
                ? route('admin.reviews.show', $data['review_id'])
                : null,
            'order' => isset($data['order_id'])
                ? route('admin.orders.show', $data['order_id'])
                : null,
            default => null,
        };
    }
}
