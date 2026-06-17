<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExchangeStatus;
use App\Http\Controllers\Controller;
use App\Models\Exchange;
use App\Models\ProductVariant;
use App\Notifications\ReturnRequestProcessed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExchangeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Exchange::with('order.user', 'user');

        if ($user->isManager() && $user->branch_id) {
            $query->whereHas('order', fn($q) => $q->where('branch_id', $user->branch_id));
        }

        $exchanges = $query->latest()->paginate(20);
        return view('admin.exchanges.index', compact('exchanges'));
    }

    public function show(Exchange $exchange)
    {
        $exchange->load('order.items.variant.product', 'order.payment', 'order.user', 'user');
        return view('admin.exchanges.show', compact('exchange'));
    }

    public function approve(Request $request, Exchange $exchange)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('admin.exchanges.show', $exchange);
        }

        if ($exchange->status !== ExchangeStatus::Pending->value) {
            return back()->with('error', 'تم معالجة طلب الاستبدال مسبقاً.');
        }

        $request->validate(['admin_note' => 'nullable|string|max:1000']);

        $exchange->load('order.items.variant.branches');

        DB::transaction(function () use ($exchange, $request) {
            $exchange->update([
                'status' => ExchangeStatus::Approved->value,
                'admin_note' => $request->admin_note,
                'approved_at' => now(),
            ]);

            $order = $exchange->order;
            $branchId = $order->branch_id ?? 1;

            // Batch-load all new variants needed for exchange
            $newVariantIds = collect($exchange->items ?? [])->pluck('new_variant_id')->filter()->unique()->all();
            $newVariants = !empty($newVariantIds)
                ? ProductVariant::with('branches')->whereIn('id', $newVariantIds)->get()->keyBy('id')
                : collect();

            foreach ($exchange->items ?? [] as $item) {
                $orderItem = $order->items->firstWhere('id', $item['order_item_id']);
                if (!$orderItem) continue;

                // Restore old variant stock
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

                // Deduct new variant stock
                $newVariant = $newVariants->get($item['new_variant_id']);
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
                $exchange->user->notify(new \App\Notifications\ExchangeApproved($order, $exchange));
            } catch (\Exception $e) {
                \Log::error('Exchange approval notification failed: ' . $e->getMessage());
            }
        });

        return redirect()->route('admin.exchanges.index')->with('success', 'تمت الموافقة على طلب الاستبدال.');
    }

    public function reject(Request $request, Exchange $exchange)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('admin.exchanges.show', $exchange);
        }

        if ($exchange->status !== ExchangeStatus::Pending->value) {
            return back()->with('error', 'تم معالجة طلب الاستبدال مسبقاً.');
        }

        $request->validate(['admin_note' => 'required|string|max:1000']);

        $exchange->update([
            'status' => ExchangeStatus::Rejected->value,
            'admin_note' => $request->admin_note,
            'rejected_at' => now(),
        ]);

        try {
            $exchange->user->notify(new ReturnRequestProcessed($exchange));
        } catch (\Exception $e) {
            \Log::error('Exchange reject notification failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.exchanges.index')->with('success', 'تم رفض طلب الاستبدال.');
    }
}
