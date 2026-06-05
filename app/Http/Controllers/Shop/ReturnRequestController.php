<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    public function index()
    {
        $returns = auth()->user()->returnRequests()->with('order')->latest()->paginate(10);
        return view('shop.returns.index', compact('returns'));
    }

    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return back()->with('error', __('return.can_only_return_delivered'));
        }

        if ($order->delivered_at && $order->delivered_at->diffInDays(now()) > 3) {
            return back()->with('error', __('return.return_period_expired'));
        }

        if ($order->returnRequests()->where('status', 'pending')->exists()) {
            return back()->with('error', __('return.already_requested'));
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $return = $order->returnRequests()->create([
            'user_id' => auth()->id(),
            'status' => 'pending',
            'reason' => $validated['reason'],
        ]);

        // Notify all admins
        $admins = \App\Models\User::whereIn('role', ['super_admin', 'manager'])->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new \App\Notifications\ReturnRequestSubmitted($return));
            } catch (\Exception $e) {
                \Log::error('Return submission notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('returns.index')->with('success', __('return.request_submitted'));
    }
}
