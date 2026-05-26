<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Branch;
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user', 'branch');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        $orders = $query->latest()->paginate(20);
        $branches = Branch::all();
        return view('admin.orders.index', compact('orders', 'branches'));
    }

    public function show(Order $order)
    {
        $order->load('items.variant.product', 'payment', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function invoice(Order $order)
    {
        $order->load('items.variant.product', 'payment', 'user');
        return view('admin.orders.invoice', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return back()->with('error', 'حالة الطلب لم تتغير.');
        }

        DB::transaction(function () use ($order, $newStatus, $oldStatus) {
            $order->update(['status' => $newStatus]);

            // Restore stock if the order is cancelled or returned
            if (in_array($newStatus, ['cancelled', 'returned']) && !in_array($oldStatus, ['cancelled', 'returned'])) {
                foreach ($order->items as $item) {
                    $variant = $item->variant;
                    if ($variant) {
                        $branchId = $order->branch_id ?? 1;
                        $pivot = $variant->branches()->where('branch_id', $branchId)->first();
                        if ($pivot) {
                            $variant->branches()->updateExistingPivot($branchId, [
                                'stock' => $pivot->pivot->stock + $item->quantity,
                            ]);
                        }
                    }
                }
            }

            // Send notification to customer
            $order->user->notify(new OrderStatusChanged($order, $newStatus));
        });

        return back()->with('success', 'تم تحديث حالة الطلب وإرسال إشعار للعميل بنجاح.');
    }
}
