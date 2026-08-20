<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryCount;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryCountController extends Controller
{
    public function index()
    {
        $counts = InventoryCount::with(['branch', 'counter'])
            ->latest()
            ->paginate(20);

        return view('admin.inventory.index', compact('counts'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.inventory.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $count = InventoryCount::create([
            'branch_id' => $request->branch_id,
            'counted_by' => auth()->id(),
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $variants = ProductVariant::where('is_active', true)
            ->with('product')
            ->get();

        $items = [];
        foreach ($variants as $variant) {
            $branchStock = $variant->branches()
                ->where('branch_id', $request->branch_id)
                ->first()?->pivot->stock ?? 0;

            $items[] = [
                'inventory_count_id' => $count->id,
                'product_variant_id' => $variant->id,
                'system_stock' => $branchStock,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('inventory_count_items')->insert($items);

        return redirect()->route('admin.inventory.show', $count);
    }

    public function show(InventoryCount $inventory)
    {
        $inventory->load(['branch', 'counter', 'items.variant.product']);

        $items = $inventory->items()
            ->with('variant.product')
            ->get();

        $stats = [
            'total_items' => $items->count(),
            'matched' => $items->where('counted_stock', '!=', null)->where('difference', 0)->count(),
            'over' => $items->where('difference', '>', 0)->count(),
            'under' => $items->where('difference', '<', 0)->count(),
            'pending' => $items->where('counted_stock', null)->count(),
        ];

        return view('admin.inventory.show', compact('inventory', 'items', 'stats'));
    }

    public function updateItem(Request $request, InventoryCount $inventory, InventoryCountItem $item)
    {
        $request->validate([
            'counted_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $item->update([
            'counted_stock' => $request->counted_stock,
            'difference' => $request->counted_stock - $item->system_stock,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'تم تحديث العدد.');
    }

    public function complete(InventoryCount $inventory)
    {
        $pendingItems = $inventory->items()->whereNull('counted_stock')->count();

        if ($pendingItems > 0) {
            return back()->withErrors(['status' => "يوجد {$pendingItems} صنف لم يتم جرده بعد."]);
        }

        $inventory->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.inventory.show', $inventory)
            ->with('success', 'تم إتمام الجرد بنجاح.');
    }

    public function destroy(InventoryCount $inventory)
    {
        if ($inventory->status === 'completed') {
            return back()->withErrors(['status' => 'لا يمكن حذف جرد مكتمل.']);
        }

        $inventory->delete();
        return redirect()->route('admin.inventory.index')
            ->with('success', 'تم حذف الجرد.');
    }
}
