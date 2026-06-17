<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CursorService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
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

        $result = CursorService::applyCursor(
            auth()->user()->notifications(),
            $request->get('cursor'),
            'created_at',
            'desc',
            20
        );
        $notifications = $result['data'];
        return view('admin.notifications.index', compact('notifications', 'result'));
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
        $latest = auth()->user()->notifications()->latest()->first();

        return response()->json([
            'count' => auth()->user()->unreadNotifications->count(),
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
            'order' => isset($data['order_id'])
                ? route('admin.orders.show', $data['order_id'])
                : null,
            default => null,
        };
    }
}
