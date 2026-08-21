<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\StockMovement;
use App\Models\ProductVariant;
use App\Events\StockUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockCountController extends Controller
{
    public function index(Request $request)
    {
        $query = StockCount::with('branch', 'creator');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $counts = $query->latest()->paginate(20)->appends($request->query());
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.stock_counts.index', compact('counts', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('admin.stock_counts.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string',
        ]);

        $branch = Branch::findOrFail($data['branch_id']);
        $variants = $branch->productVariants()->get();

        if ($variants->isEmpty()) {
            return back()->with('error', __('global.sc_no_products_in_branch'));
        }

        $count = StockCount::create([
            'branch_id' => $data['branch_id'],
            'created_by' => auth()->id(),
            'status' => 'in_progress',
            'notes' => $data['notes'] ?? null,
        ]);

        $items = [];
        foreach ($variants as $variant) {
            $items[] = [
                'stock_count_id' => $count->id,
                'product_variant_id' => $variant->id,
                'system_stock' => $variant->pivot->stock,
                'counted_stock' => null,
                'difference' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        StockCountItem::insert($items);

        return redirect()->route('admin.stock-counts.show', $count)
            ->with('success', __('global.sc_created'));
    }

    public function show(StockCount $stockCount)
    {
        $stockCount->load('branch', 'creator', 'items.variant.product');

        $totalSystem = $stockCount->items->sum('system_stock');
        $totalCounted = $stockCount->items->whereNotNull('counted_stock')->sum('counted_stock');
        $totalDiff = $stockCount->items->whereNotNull('difference')->sum('difference');
        $countedItems = $stockCount->items->whereNotNull('counted_stock')->count();
        $totalItems = $stockCount->items->count();

        return view('admin.stock_counts.show', compact('stockCount', 'totalSystem', 'totalCounted', 'totalDiff', 'countedItems', 'totalItems'));
    }

    public function updateItem(Request $request, StockCount $stockCount, StockCountItem $item)
    {
        if ($stockCount->status !== 'in_progress') {
            return back()->with('error', __('global.sc_not_in_progress'));
        }

        if ($item->stock_count_id !== $stockCount->id) {
            return back()->with('error', __('global.sc_invalid_item'));
        }

        $data = $request->validate([
            'counted_stock' => 'required|integer|min:0',
        ]);

        $item->update([
            'counted_stock' => $data['counted_stock'],
            'difference' => $data['counted_stock'] - $item->system_stock,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => __('global.sc_item_updated'),
                'difference' => $item->difference,
            ]);
        }

        return back()->with('success', __('global.sc_item_updated'));
    }

    public function complete(StockCount $stockCount)
    {
        if ($stockCount->status !== 'in_progress') {
            return back()->with('error', __('global.sc_not_in_progress'));
        }

        $unCounted = $stockCount->items()->whereNull('counted_stock')->count();
        if ($unCounted > 0) {
            return back()->with('error', __('global.sc_has_uncounted', ['count' => $unCounted]));
        }

        $branchId = $stockCount->branch_id;

        DB::beginTransaction();
        try {
            foreach ($stockCount->items as $item) {
                if ($item->difference == 0) continue;

                $variant = $item->variant;
                $stockBefore = $item->system_stock;
                $stockAfter = $item->counted_stock;

                DB::table('branch_product_variant')
                    ->where('product_variant_id', $variant->id)
                    ->where('branch_id', $branchId)
                    ->update(['stock' => $stockAfter]);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'branch_id' => $branchId,
                    'type' => StockMovementType::Adjustment->value,
                    'quantity' => $item->difference,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'created_by' => auth()->id(),
                ]);

                StockUpdated::dispatch(
                    variantId: $variant->id,
                    productId: $variant->product_id,
                    branchId: $branchId,
                    stockBefore: $stockBefore,
                    stockAfter: $stockAfter,
                    action: StockMovementType::Adjustment->value,
                );
            }

            $stockCount->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.stock-counts.show', $stockCount)
                ->with('success', __('global.sc_completed'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(StockCount $stockCount)
    {
        if (!in_array($stockCount->status, ['draft', 'in_progress'])) {
            return back()->with('error', __('global.sc_cannot_cancel'));
        }

        $stockCount->update(['status' => 'cancelled']);

        return back()->with('success', __('global.sc_cancelled'));
    }

    public function destroy(StockCount $stockCount)
    {
        if ($stockCount->status === 'completed') {
            return back()->with('error', __('global.sc_cannot_delete'));
        }

        $stockCount->items()->delete();
        $stockCount->delete();

        return redirect()->route('admin.stock-counts.index')
            ->with('success', __('global.sc_deleted'));
    }
}
