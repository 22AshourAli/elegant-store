<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = ReturnRequest::with('order.user', 'user');

        if ($user->isManager() && $user->branch_id) {
            $query->whereHas('order', fn($q) => $q->where('branch_id', $user->branch_id));
        }

        $returns = (clone $query)->where('type', 'return')->latest()->paginate(20);
        return view('admin.returns.index', compact('returns'));
    }

    public function show(ReturnRequest $return)
    {
        $return->load('order.items.variant.product', 'order.payment', 'order.user', 'user');
        return view('admin.returns.show', compact('return'));
    }

    public function approve(Request $request, ReturnRequest $return)
    {
        if ($return->status !== 'pending') {
            return back()->with('error', 'تم معالجة الطلب مسبقاً.');
        }

        $request->validate(['admin_note' => 'nullable|string|max:1000']);

        $return->load('order.items.variant');

        DB::transaction(function () use ($return, $request) {
            $return->update([
                'status' => 'approved',
                'admin_note' => $request->admin_note,
                'approved_at' => now(),
            ]);

            $order = $return->order;

            if ($return->type === 'exchange') {
                // Exchange: return old items, deduct new items
                $branchId = $order->branch_id ?? 1;

                foreach ($return->exchange_data ?? [] as $exchangeItem) {
                    $orderItem = $order->items()->find($exchangeItem['order_item_id']);
                    if (!$orderItem) continue;

                    // Restore old variant stock
                    $oldVariant = $orderItem->variant;
                    if ($oldVariant) {
                        $pivot = $oldVariant->branches()->where('branch_id', $branchId)->first();
                        if ($pivot) {
                            $oldVariant->branches()->updateExistingPivot($branchId, [
                                'stock' => $pivot->pivot->stock + $orderItem->quantity,
                            ]);
                        }
                    }

                    // Deduct new variant stock
                    $newVariant = \App\Models\ProductVariant::find($exchangeItem['new_variant_id']);
                    if ($newVariant) {
                        $pivot = $newVariant->branches()->where('branch_id', $branchId)->first();
                        if ($pivot && $pivot->pivot->stock >= $orderItem->quantity) {
                            $newVariant->branches()->updateExistingPivot($branchId, [
                                'stock' => $pivot->pivot->stock - $orderItem->quantity,
                            ]);
                        }
                    }
                }

                try {
                    $order->user->notify(new \App\Notifications\ExchangeApproved($order, $return));
                } catch (\Exception $e) {
                    \Log::error('Exchange notification failed: ' . $e->getMessage());
                }
            } else {
                // Return: restore stock for all items
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

                // Mark order as returned only for returns (not exchanges)
                $order->update(['status' => 'returned']);

                try {
                    $order->user->notify(new OrderStatusChanged($order, 'returned'));
                } catch (\Exception $e) {
                    \Log::error('Return notification failed: ' . $e->getMessage());
                }
            }
        });

        $msg = $return->type === 'exchange'
            ? 'تمت الموافقة على طلب الاستبدال وتبديل المخزون.'
            : 'تمت الموافقة على طلب الإرجاع وإعادة المخزون.';

        return redirect()->route('admin.returns.index')->with('success', $msg);
    }

    public function reject(Request $request, ReturnRequest $return)
    {
        if ($return->status !== 'pending') {
            return back()->with('error', 'تم معالجة الطلب مسبقاً.');
        }

        $request->validate(['admin_note' => 'required|string|max:1000']);

        $return->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
            'rejected_at' => now(),
        ]);

        // Notify customer
        try {
            $return->user->notify(new \App\Notifications\ReturnRequestProcessed($return));
        } catch (\Exception $e) {
            \Log::error('Reject notification failed: ' . $e->getMessage());
        }

        $msg = $return->type === 'exchange' ? 'تم رفض طلب الاستبدال.' : 'تم رفض طلب الإرجاع.';
        return redirect()->route('admin.returns.index')->with('success', $msg);
    }
}
