<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockTransferStatus;
use App\Enums\StockMovementType;
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
            'status' => StockTransferStatus::Pending->value,
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
        if ($stockTransfer->status !== StockTransferStatus::Pending->value) {
            return back()->with('error', __('global.st_cannot_complete'));
        }

        $stockTransfer->load('items.variant.branches');

        $movements = [];

        foreach ($stockTransfer->items as $item) {
            $variant = $item->variant;

            $fromBranch = $variant->branches->firstWhere('id', $stockTransfer->from_branch_id);
            if (!$fromBranch || $fromBranch->pivot->stock < $item->quantity) {
                return back()->with('error', __('global.st_insufficient_stock', [
                    'variant' => $variant->sku,
                    'available' => $fromBranch ? $fromBranch->pivot->stock : 0,
                ]));
            }

            $fromStockBefore = $fromBranch->pivot->stock;
            $fromStockAfter = $fromStockBefore - $item->quantity;
            $variant->branches()->updateExistingPivot($stockTransfer->from_branch_id, [
                'stock' => $fromStockAfter,
            ]);

            $movements[] = [
                'product_variant_id' => $variant->id,
                'branch_id' => $stockTransfer->from_branch_id,
                'type' => StockMovementType::TransferOut->value,
                'quantity' => -$item->quantity,
                'stock_before' => $fromStockBefore,
                'stock_after' => $fromStockAfter,
            ];

            StockUpdated::dispatch(
                variantId: $variant->id,
                productId: $variant->product_id,
                branchId: $stockTransfer->from_branch_id,
                stockBefore: $fromStockBefore,
                stockAfter: $fromStockAfter,
                action: StockMovementType::TransferOut->value,
            );

            $toBranch = $variant->branches->firstWhere('id', $stockTransfer->to_branch_id);
            if ($toBranch) {
                $toStockBefore = $toBranch->pivot->stock;
                $toStockAfter = $toStockBefore + $item->quantity;
                $variant->branches()->updateExistingPivot($stockTransfer->to_branch_id, [
                    'stock' => $toStockAfter,
                ]);

                $movements[] = [
                    'product_variant_id' => $variant->id,
                    'branch_id' => $stockTransfer->to_branch_id,
                    'type' => StockMovementType::TransferIn->value,
                    'quantity' => $item->quantity,
                    'stock_before' => $toStockBefore,
                    'stock_after' => $toStockAfter,
                ];

                StockUpdated::dispatch(
                    variantId: $variant->id,
                    productId: $variant->product_id,
                    branchId: $stockTransfer->to_branch_id,
                    stockBefore: $toStockBefore,
                    stockAfter: $toStockAfter,
                    action: StockMovementType::TransferIn->value,
                );
            } else {
                $variant->branches()->attach($stockTransfer->to_branch_id, ['stock' => $item->quantity]);
            }
        }

        StockMovement::insert($movements);

        $stockTransfer->update([
            'status' => StockTransferStatus::Completed->value,
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.stock-transfers.show', $stockTransfer)
            ->with('success', __('global.st_completed'));
    }

    public function cancel(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== StockTransferStatus::Pending->value) {
            return back()->with('error', __('global.st_cannot_cancel'));
        }

        $stockTransfer->update(['status' => StockTransferStatus::Cancelled->value]);

        return back()->with('success', __('global.st_cancelled'));
    }

    public function destroy(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status === StockTransferStatus::Completed->value) {
            return back()->with('error', __('global.st_cannot_delete'));
        }

        $stockTransfer->items()->delete();
        $stockTransfer->delete();

        return redirect()->route('admin.stock-transfers.index')
            ->with('success', __('global.st_deleted'));
    }
}
