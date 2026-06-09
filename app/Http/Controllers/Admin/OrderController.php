<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\OrderDelivered;
use App\Models\Order;
use App\Models\Branch;
use App\Models\ReturnRequest;
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user', 'branch');
        $user = auth()->user();

        if ($user->isManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->latest()->paginate(20);
        $branches = Cache::remember('admin_branches', 3600, fn() => Branch::all());
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
            $updateData = ['status' => $newStatus];

            // Auto-set delivered_at when status changes to delivered
            if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                $updateData['delivered_at'] = now();
            }

            $order->update($updateData);

            // Dispatch event for cashback on first delivered order
            if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                event(new OrderDelivered($order));
            }

            // Restore stock if the order is cancelled or returned
            if (in_array($newStatus, ['cancelled', 'returned']) && !in_array($oldStatus, ['cancelled', 'returned'])) {
                $order->load('items.variant.branches');
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

                // Auto-create ReturnRequest if status is 'returned'
                if ($newStatus === 'returned') {
                    $existing = ReturnRequest::where('order_id', $order->id)->first();
                    if (!$existing) {
                        ReturnRequest::create([
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'status' => 'approved',
                            'reason' => 'تم الإرجاع بواسطة الإدارة',
                            'approved_at' => now(),
                        ]);
                    }
                }
            }

            // Send notification to customer
            $order->user->notify(new OrderStatusChanged($order, $newStatus));
        });

        return back()->with('success', 'تم تحديث حالة الطلب وإرسال إشعار للعميل بنجاح.');
    }
}
