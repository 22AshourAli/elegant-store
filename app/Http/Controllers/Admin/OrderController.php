<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\OrderDelivered;
use App\Events\StockUpdated;
use App\Models\Branch;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\StockMovement;
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
            'status' => 'required|in:pending,confirmed,processing,shipped,out_for_delivery,delivered,returned,collected,cancelled'
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
                            $stockBefore = $pivot->pivot->stock;
                            $stockAfter = $stockBefore + $item->quantity;
                            $variant->branches()->updateExistingPivot($branchId, [
                                'stock' => $stockAfter,
                            ]);

                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => $branchId,
                                'order_id' => $order->id,
                                'type' => 'return',
                                'quantity' => $item->quantity,
                                'stock_before' => $stockBefore,
                                'stock_after' => $stockAfter,
                            ]);

                            StockUpdated::dispatch(
                                variantId: $variant->id,
                                productId: $variant->product_id,
                                branchId: $branchId,
                                stockBefore: $stockBefore,
                                stockAfter: $stockAfter,
                                action: 'return',
                                orderId: $order->id,
                            );
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

        });

        // Send notification outside transaction so mail failure doesn't rollback the order update
        try {
            $order->user->notify(new OrderStatusChanged($order, $newStatus));
        } catch (\Throwable $e) {
            // Notification sent best-effort; mail may fail (e.g. SMTP timeout)
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'تم تحديث حالة الطلب وإرسال إشعار للعميل بنجاح.',
                'status' => $newStatus,
                'old_status' => $oldStatus,
            ]);
        }

        return back()->with('success', 'تم تحديث حالة الطلب وإرسال إشعار للعميل بنجاح.');
    }
}
