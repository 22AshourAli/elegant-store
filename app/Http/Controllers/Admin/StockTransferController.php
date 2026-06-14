<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\StockTransfer;
use App\Models\StockMovement;
use App\Events\StockUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockTransferController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::with('fromBranch', 'toBranch', 'creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.stock_transfers.index', compact('transfers'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $variants = ProductVariant::with('product')->orderBy('sku')->get();

        return view('admin.stock_transfers.create', compact('branches', 'variants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_branch_id' => 'required|exists:branches,id|different:to_branch_id',
            'to_branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $transfer = StockTransfer::create([
            'reference_number' => 'ST-' . strtoupper(Str::random(8)),
            'from_branch_id' => $data['from_branch_id'],
            'to_branch_id' => $data['to_branch_id'],
            'created_by' => auth()->id(),
            'status' => 'pending',
            'notes' => $data['notes'],
        ]);

        $items = [];
        foreach ($data['items'] as $item) {
            $items[] = [
                'product_variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
            ];
        }

        $transfer->items()->createMany($items);

        return redirect()->route('admin.stock-transfers.show', $transfer)
            ->with('success', __('global.st_created'));
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load('fromBranch', 'toBranch', 'creator', 'items.variant.product');

        return view('admin.stock_transfers.show', compact('stockTransfer'));
    }

    public function complete(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->with('error', __('global.st_cannot_complete'));
        }

        $stockTransfer->load('items.variant');

        foreach ($stockTransfer->items as $item) {
            $variant = $item->variant;

            // Deduct from source branch
            $fromPivot = $variant->branches()->where('branch_id', $stockTransfer->from_branch_id)->first();
            if (!$fromPivot || $fromPivot->pivot->stock < $item->quantity) {
                return back()->with('error', __('global.st_insufficient_stock', [
                    'variant' => $variant->sku,
                    'available' => $fromPivot ? $fromPivot->pivot->stock : 0,
                ]));
            }

            $fromStockBefore = $fromPivot->pivot->stock;
            $fromStockAfter = $fromStockBefore - $item->quantity;
            $variant->branches()->updateExistingPivot($stockTransfer->from_branch_id, [
                'stock' => $fromStockAfter,
            ]);

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'branch_id' => $stockTransfer->from_branch_id,
                'type' => 'transfer_out',
                'quantity' => -$item->quantity,
                'stock_before' => $fromStockBefore,
                'stock_after' => $fromStockAfter,
            ]);

            StockUpdated::dispatch(
                variantId: $variant->id,
                productId: $variant->product_id,
                branchId: $stockTransfer->from_branch_id,
                stockBefore: $fromStockBefore,
                stockAfter: $fromStockAfter,
                action: 'transfer_out',
            );

            // Add to destination branch
            $toPivot = $variant->branches()->where('branch_id', $stockTransfer->to_branch_id)->first();
            if ($toPivot) {
                $toStockBefore = $toPivot->pivot->stock;
                $toStockAfter = $toStockBefore + $item->quantity;
                $variant->branches()->updateExistingPivot($stockTransfer->to_branch_id, [
                    'stock' => $toStockAfter,
                ]);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'branch_id' => $stockTransfer->to_branch_id,
                    'type' => 'transfer_in',
                    'quantity' => $item->quantity,
                    'stock_before' => $toStockBefore,
                    'stock_after' => $toStockAfter,
                ]);

                StockUpdated::dispatch(
                    variantId: $variant->id,
                    productId: $variant->product_id,
                    branchId: $stockTransfer->to_branch_id,
                    stockBefore: $toStockBefore,
                    stockAfter: $toStockAfter,
                    action: 'transfer_in',
                );
            } else {
                $variant->branches()->attach($stockTransfer->to_branch_id, ['stock' => $item->quantity]);
            }
        }

        $stockTransfer->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.stock-transfers.show', $stockTransfer)
            ->with('success', __('global.st_completed'));
    }

    public function cancel(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->with('error', __('global.st_cannot_cancel'));
        }

        $stockTransfer->update(['status' => 'cancelled']);

        return back()->with('success', __('global.st_cancelled'));
    }

    public function destroy(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status === 'completed') {
            return back()->with('error', __('global.st_cannot_delete'));
        }

        $stockTransfer->items()->delete();
        $stockTransfer->delete();

        return redirect()->route('admin.stock-transfers.index')
            ->with('success', __('global.st_deleted'));
    }
}
