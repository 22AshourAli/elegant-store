<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
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
        if ($order->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بإلغاء هذا الطلب.');
        }

        if (!in_array($order->status, [OrderStatus::Pending->value, OrderStatus::Confirmed->value])) {
            return redirect()->route('orders.show', $order)->with('error', __('لا يمكن إلغاء طلب في هذه الحالة.'));
        }

        try {
            $order->update(['status' => OrderStatus::Cancelled->value]);

            $order->load('items.variant');

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

            $admins = \App\Models\User::whereIn('role', array_map(fn($r) => $r->value, UserRole::adminRoles()))->get();
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new \App\Notifications\OrderStatusChanged($order, OrderStatus::Cancelled->value));
                } catch (\Throwable $e) {
                    \Log::error('Cancel notif failed for ' . $admin->email . ': ' . $e->getMessage());
                }
            }

            return redirect()->route('orders.show', $order)->with('success', __('تم إلغاء الطلب بنجاح.'));
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            flash()->error(__('global.server_error'));
            return redirect()->back();
        }
    }
}
