<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if (request()->query('json')) {
            $paginator = auth()->user()->notifications()->latest()->paginate(15);

            $notifications = $paginator->getCollection()->map(function ($n) {
                $data = $n->data;
                $type = $this->resolveType($data);
                return [
                    'id'      => $n->id,
                    'title'   => $data['title'] ?? $data['message'] ?? '',
                    'time'    => $n->created_at->diffForHumans(),
                    'read_at' => $n->read_at,
                    'type'    => $type,
                    'url'     => $this->resolveUrl($data, $type),
                ];
            });

            return response()->json([
                'notifications'   => $notifications,
                'next_page_url'   => $paginator->nextPageUrl(),
                'current_page'    => $paginator->currentPage(),
                'last_page'       => $paginator->lastPage(),
            ]);
        }

        $notifications = auth()->user()->notifications()->latest()->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back()->with('success', __('global.admin_mark_read'));
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', __('global.admin_mark_all_read'));
    }

    public function unreadCount()
    {
        $user = auth()->user();
        $unreadQuery = $user->unreadNotifications();
        $unreadCount = (clone $unreadQuery)->count();
        $orderCount = (clone $unreadQuery)->whereRaw("JSON_EXTRACT(data, '$.order_id') IS NOT NULL")->count();
        $latest = $user->notifications()->latest()->first();

        return response()->json([
            'count' => $unreadCount,
            'order_count' => $orderCount,
            'latest_id' => $latest?->id,
            'latest_created_at' => $latest?->created_at?->toIso8601String(),
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
