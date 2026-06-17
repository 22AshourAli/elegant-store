<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\ReturnRequestStatus;
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
        if ($return->status !== ReturnRequestStatus::Pending->value) {
            return back()->with('error', 'تم معالجة الطلب مسبقاً.');
        }

        $request->validate(['admin_note' => 'nullable|string|max:1000']);

        $return->load('order.items.variant.branches');

        DB::transaction(function () use ($return, $request) {
            $return->update([
                'status' => ReturnRequestStatus::Approved->value,
                'admin_note' => $request->admin_note,
                'approved_at' => now(),
            ]);

            $order = $return->order;

            if ($return->type === 'exchange') {
                $this->processExchange($return, $order);
            } else {
                $this->processReturn($order);
            }
        });

        $msg = $return->type === 'exchange'
            ? 'تمت الموافقة على طلب الاستبدال وتبديل المخزون.'
            : 'تمت الموافقة على طلب الإرجاع وإعادة المخزون.';

        return redirect()->route('admin.returns.index')->with('success', $msg);
    }

    public function reject(Request $request, ReturnRequest $return)
    {
        if ($return->status !== ReturnRequestStatus::Pending->value) {
            return back()->with('error', 'تم معالجة الطلب مسبقاً.');
        }

        $request->validate(['admin_note' => 'required|string|max:1000']);

        $return->update([
            'status' => ReturnRequestStatus::Rejected->value,
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

    private function processExchange(ReturnRequest $return, Order $order): void
    {
        $branchId = $order->branch_id ?? 1;

        $newVariantIds = collect($return->exchange_data ?? [])->pluck('new_variant_id')->filter()->unique()->all();
        $newVariants = !empty($newVariantIds)
            ? \App\Models\ProductVariant::with('branches')->whereIn('id', $newVariantIds)->get()->keyBy('id')
            : collect();

        foreach ($return->exchange_data ?? [] as $exchangeItem) {
            $orderItem = $order->items->firstWhere('id', $exchangeItem['order_item_id']);
            if (!$orderItem) continue;

            $oldVariant = $orderItem->variant;
            if ($oldVariant) {
                $branch = $oldVariant->branches->first(fn($b) => $b->id == $branchId);
                if ($branch) {
                    DB::table('branch_product_variant')
                        ->where('product_variant_id', $oldVariant->id)
                        ->where('branch_id', $branchId)
                        ->update(['stock' => $branch->pivot->stock + $orderItem->quantity]);
                }
            }

            $newVariant = $newVariants->get($exchangeItem['new_variant_id']);
            if ($newVariant) {
                $branch = $newVariant->branches->first(fn($b) => $b->id == $branchId);
                if ($branch && $branch->pivot->stock >= $orderItem->quantity) {
                    DB::table('branch_product_variant')
                        ->where('product_variant_id', $newVariant->id)
                        ->where('branch_id', $branchId)
                        ->update(['stock' => $branch->pivot->stock - $orderItem->quantity]);
                }
            }
        }

        try {
            $order->user->notify(new \App\Notifications\ExchangeApproved($order, $return));
        } catch (\Exception $e) {
            \Log::error('Exchange notification failed: ' . $e->getMessage());
        }
    }

    private function processReturn(Order $order): void
    {
        $branchId = $order->branch_id ?? 1;

        foreach ($order->items as $item) {
            $variant = $item->variant;
            if (!$variant) continue;

            $branch = $variant->branches->first(fn($b) => $b->id == $branchId);
            if ($branch) {
                DB::table('branch_product_variant')
                    ->where('product_variant_id', $variant->id)
                    ->where('branch_id', $branchId)
                    ->update(['stock' => $branch->pivot->stock + $item->quantity]);
            }
        }

        $order->update(['status' => OrderStatus::Returned->value]);

        try {
            $order->user->notify(new OrderStatusChanged($order, OrderStatus::Returned->value));
        } catch (\Exception $e) {
            \Log::error('Return notification failed: ' . $e->getMessage());
        }
    }
}
