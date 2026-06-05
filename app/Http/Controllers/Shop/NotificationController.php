<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        if (request()->query('json')) {
            $notifications = auth()->user()->notifications()->latest()->take(10)->get()->map(function ($n) {
                $data = $n->data;
                $type = $this->resolveType($data);
                return [
                    'id' => $n->id,
                    'title' => $data['title'] ?? $data['message'] ?? '',
                    'time' => $n->created_at->diffForHumans(),
                    'read_at' => $n->read_at,
                    'type' => $type,
                    'url' => $this->resolveUrl($data, $type),
                ];
            });

            return response()->json([
                'notifications' => $notifications,
            ]);
        }

        auth()->user()->unreadNotifications->markAsRead();

        $notifications = auth()->user()->notifications()->paginate(20);

        return view('shop.notifications.index', compact('notifications'));
    }

    public function markRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications->count()
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
        if (isset($data['order_id'])) {
            return 'order';
        }
        return 'info';
    }

    private function resolveUrl(array $data, string $type): ?string
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isManager();

        if ($isAdmin) {
            return match ($type) {
                'exchange' => isset($data['exchange_id'])
                    ? route('admin.exchanges.show', $data['exchange_id'])
                    : (isset($data['return_request_id'])
                        ? route('admin.exchanges.show', $data['return_request_id'])
                        : null),
                'return' => isset($data['return_request_id'])
                    ? route('admin.returns.show', $data['return_request_id'])
                    : null,
                'order' => isset($data['order_id'])
                    ? route('admin.orders.show', $data['order_id'])
                    : null,
                default => null,
            };
        }

        return match ($type) {
            'exchange', 'exchange_approved', 'exchange_submitted' => route('exchanges.index'),
            'return' => route('returns.index'),
            'order' => isset($data['order_id'])
                ? route('orders.show', $data['order_id'])
                : null,
            default => null,
        };
    }
}
