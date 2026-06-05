<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        if (request()->query('json')) {
            $notifications = auth()->user()->notifications()->latest()->take(10)->get()->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? $n->data['message'] ?? '',
                    'time' => $n->created_at->diffForHumans(),
                    'read_at' => $n->read_at,
                    'type' => isset($n->data['order_id']) ? 'order' : 'info',
                    'order_url' => isset($n->data['order_id']) ? route('admin.orders.show', $n->data['order_id']) : null,
                ];
            });

            return response()->json([
                'notifications' => $notifications,
            ]);
        }

        $notifications = auth()->user()->notifications()->paginate(20);
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
        return response()->json([
            'count' => auth()->user()->unreadNotifications->count()
        ]);
    }
}
