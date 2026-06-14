<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\StockUpdated;
use App\Models\Exchange;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosReturnController extends Controller
{
    public function index()
    {
        return view('admin.pos.return');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $orders = Order::with(['items.variant', 'user', 'cashier'])
            ->where('order_type', 'offline')
            ->where(function ($q) use ($query) {
                $q->where('id', $query)
                  ->orWhere('phone', $query)
                  ->orWhereHas('user', fn($uq) => $uq->where('phone', $query));
            })
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'customer_name' => $order->user?->name ?? $order->phone ?? __('global.pos_guest'),
                'cashier' => $order->cashier?->name,
                'date' => $order->created_at->format('Y-m-d H:i'),
                'total' => (float) $order->total,
                'payment_method' => $order->payment_method,
                'status' => $order->status,
                'items' => $order->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'color' => $item->color,
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                    'returned_qty' => $item->returned_qty ?? 0,
                    'returnable' => max(0, $item->quantity - ($item->returned_qty ?? 0)),
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->total,
                    'image' => $item->variant?->imageUrl(),
                    'variant_id' => $item->product_variant_id,
                ]),
                'has_returnable' => $order->items->contains(fn($i) => ($i->quantity - ($i->returned_qty ?? 0)) > 0),
            ]);

        return response()->json($orders);
    }

    public function process(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:return,exchange',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'exchange_items' => 'required_if:type,exchange|array',
            'exchange_items.*.variant_id' => 'required_if:type,exchange|exists:product_variants,id',
            'exchange_items.*.quantity' => 'required_if:type,exchange|integer|min:1',
            'payment_method' => 'required|in:cash,card,wallet',
        ]);

        $order = Order::with('items.variant.branches')->findOrFail($request->order_id);

        if ($order->order_type !== 'offline') {
            return response()->json(['error' => __('global.pos_return_offline_only')], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $order) {
                $totalRefund = 0;
                $totalExchange = 0;
                $allFullyReturned = true;

                foreach ($request->items as $item) {
                    $orderItem = $order->items()->find($item['order_item_id']);
                    if (!$orderItem) {
                        throw new \Exception(__('global.pos_return_item_not_found'));
                    }

                    $returnable = $orderItem->quantity - ($orderItem->returned_qty ?? 0);
                    if ($item['quantity'] > $returnable) {
                        throw new \Exception(__('global.pos_return_qty_exceeds', ['product' => $orderItem->product_name]));
                    }

                    $variant = $orderItem->variant;
                    if ($variant) {
                        $pivot = $variant->branches()->where('branch_id', 1)->first();
                        if ($pivot) {
                            $stockBefore = $pivot->pivot->stock;
                            $stockAfter = $stockBefore + $item['quantity'];
                            $variant->branches()->updateExistingPivot(1, [
                                'stock' => $stockAfter,
                            ]);

                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => 1,
                                'order_id' => $order->id,
                                'type' => 'return',
                                'quantity' => $item['quantity'],
                                'stock_before' => $stockBefore,
                                'stock_after' => $stockAfter,
                            ]);

                            StockUpdated::dispatch(
                                variantId: $variant->id,
                                productId: $variant->product_id,
                                branchId: 1,
                                stockBefore: $stockBefore,
                                stockAfter: $stockAfter,
                                action: 'return',
                                orderId: $order->id,
                            );
                        }
                    }

                    $orderItem->increment('returned_qty', $item['quantity']);
                    $totalRefund += (float) $orderItem->unit_price * $item['quantity'];

                    if (($orderItem->quantity - $orderItem->returned_qty) > 0) {
                        $allFullyReturned = false;
                    }
                }

                if ($allFullyReturned) {
                    $order->update(['status' => 'returned']);
                }

                $returnRequest = ReturnRequest::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id ?? auth()->id(),
                    'type' => 'return',
                    'status' => 'approved',
                    'reason' => __('global.pos_return_pos_reason'),
                    'admin_note' => __('global.pos_return_pos_note'),
                    'approved_at' => now(),
                ]);

                $exchangeId = null;

                if ($request->type === 'exchange') {
                    foreach ($request->exchange_items as $exItem) {
                        $variant = ProductVariant::with('branches')->findOrFail($exItem['variant_id']);

                        if ($variant->total_stock < $exItem['quantity']) {
                            throw new \Exception(__('global.pos_insufficient_stock', ['product' => $variant->sku]));
                        }

                        $pivot = $variant->branches()->where('branch_id', 1)->first();
                        if ($pivot && $pivot->pivot->stock >= $exItem['quantity']) {
                            $stockBefore = $pivot->pivot->stock;
                            $stockAfter = $stockBefore - $exItem['quantity'];
                            $variant->branches()->updateExistingPivot(1, [
                                'stock' => $stockAfter,
                            ]);

                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => 1,
                                'order_id' => $order->id,
                                'type' => 'exchange',
                                'quantity' => -$exItem['quantity'],
                                'stock_before' => $stockBefore,
                                'stock_after' => $stockAfter,
                            ]);

                            StockUpdated::dispatch(
                                variantId: $variant->id,
                                productId: $variant->product_id,
                                branchId: 1,
                                stockBefore: $stockBefore,
                                stockAfter: $stockAfter,
                                action: 'exchange',
                                orderId: $order->id,
                            );
                        }

                        $totalExchange += (float) $variant->current_price * $exItem['quantity'];
                    }

                    $exchange = Exchange::create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id ?? auth()->id(),
                        'status' => 'completed',
                        'reason' => __('global.pos_exchange_pos_reason'),
                        'items' => $request->exchange_items,
                        'admin_note' => __('global.pos_return_pos_note'),
                        'approved_at' => now(),
                    ]);

                    $exchangeId = $exchange->id;
                }

                return [
                    'success' => true,
                    'message' => $request->type === 'exchange'
                        ? __('global.pos_exchange_done')
                        : __('global.pos_return_done'),
                    'type' => $request->type,
                    'refund_amount' => $totalRefund,
                    'exchange_total' => $totalExchange,
                    'difference' => $totalRefund - $totalExchange,
                    'return_id' => $returnRequest->id,
                    'exchange_id' => $exchangeId,
                ];
            });

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            return redirect()->route('admin.pos.return')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
