<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Enums\ReturnRequestStatus;
use App\Http\Controllers\Controller;
use App\Events\OrderDelivered;
use App\Events\StockUpdated;
use App\Models\Branch;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\StockMovement;
use App\Notifications\OrderStatusChanged;
use App\Services\CursorService;
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

        $result = CursorService::applyCursor(
            $query->latest(),
            $request->get('cursor'),
            'created_at',
            'desc',
            20
        );
        $orders = $result['data'];
        $branches = Cache::remember('admin_branches', 3600, fn() => Branch::all());
        return view('admin.orders.index', compact('orders', 'branches', 'result'));
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

            if ($newStatus === OrderStatus::Delivered->value && $oldStatus !== OrderStatus::Delivered->value) {
                $updateData['delivered_at'] = now();
            }

            $order->update($updateData);

            if ($newStatus === OrderStatus::Delivered->value && $oldStatus !== OrderStatus::Delivered->value) {
                event(new OrderDelivered($order));
            }

            $this->restoreStockForCancelledReturn($order, $newStatus, $oldStatus);
        });

        $this->sendStatusNotification($order, $newStatus);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'تم تحديث حالة الطلب وإرسال إشعار للعميل بنجاح.',
                'status' => $newStatus,
                'old_status' => $oldStatus,
            ]);
        }

        return back()->with('success', 'تم تحديث حالة الطلب وإرسال إشعار للعميل بنجاح.');
    }

    private function restoreStockForCancelledReturn(Order $order, string $newStatus, string $oldStatus): void
    {
        if (!in_array($newStatus, [OrderStatus::Cancelled->value, OrderStatus::Returned->value]) || in_array($oldStatus, [OrderStatus::Cancelled->value, OrderStatus::Returned->value])) {
            return;
        }

        $order->load('items.variant.branches');
        $branchId = $order->branch_id ?? 1;
        $stockMovements = [];

        foreach ($order->items as $item) {
            $variant = $item->variant;
            if (!$variant) continue;

            $branch = $variant->branches->first(fn($b) => $b->id == $branchId);
            if (!$branch) continue;

            $stockBefore = $branch->pivot->stock;
            $stockAfter = $stockBefore + $item->quantity;

            DB::table('branch_product_variant')
                ->where('product_variant_id', $variant->id)
                ->where('branch_id', $branchId)
                ->update(['stock' => $stockAfter]);

            $stockMovements[] = [
                'product_variant_id' => $variant->id,
                'branch_id' => $branchId,
                'order_id' => $order->id,
                'type' => StockMovementType::Return->value,
                'quantity' => $item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            StockUpdated::dispatch(
                variantId: $variant->id,
                productId: $variant->product_id,
                branchId: $branchId,
                stockBefore: $stockBefore,
                stockAfter: $stockAfter,
                action: StockMovementType::Return->value,
                orderId: $order->id,
            );
        }

        if (!empty($stockMovements)) {
            StockMovement::insert($stockMovements);
        }

        if ($newStatus === OrderStatus::Returned->value) {
            $existing = ReturnRequest::where('order_id', $order->id)->first();
            if (!$existing) {
                ReturnRequest::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'status' => ReturnRequestStatus::Approved->value,
                    'reason' => 'تم الإرجاع بواسطة الإدارة',
                    'approved_at' => now(),
                ]);
            }
        }
    }

    private function sendStatusNotification(Order $order, string $newStatus): void
    {
        try {
            $order->user->notify(new OrderStatusChanged($order, $newStatus));
        } catch (\Throwable $e) {
        }
    }
}
