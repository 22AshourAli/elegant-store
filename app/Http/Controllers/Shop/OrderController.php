<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->with('items.variant.product', 'payment')->latest()->paginate(10);
        return view('shop.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بمشاهدة هذا الطلب.');
        }

        $order->load('items.variant.product', 'payment');
        return view('shop.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بإلغاء هذا الطلب.');
        }

        // Only allow cancellation of pending or confirmed orders
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->route('orders.show', $order)->with('error', __('لا يمكن إلغاء طلب في هذه الحالة.'));
        }

        // Cancel the order
        $order->update(['status' => 'cancelled']);

        $order->load('items.variant');

        // Restore stock for cancelled items
        foreach ($order->items as $item) {
            $branchId = $order->branch_id ?? 1;
            $variant = $item->variant;
            $pivot = $variant->branches()->where('branch_id', $branchId)->first();
            if ($pivot) {
                $variant->branches()->updateExistingPivot($branchId, [
                    'stock' => $pivot->pivot->stock + $item->quantity,
                ]);
            }
        }

        // Send notification to each admin about cancellation
        $admins = \App\Models\User::whereIn('role', ['super_admin', 'manager'])->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new \App\Notifications\OrderStatusChanged($order, 'cancelled'));
            } catch (\Throwable $e) {
                \Log::error('Cancel notif failed for ' . $admin->email . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('orders.show', $order)->with('success', __('تم إلغاء الطلب بنجاح.'));
    }
}

