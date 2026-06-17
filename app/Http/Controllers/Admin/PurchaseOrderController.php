<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\StockMovement;
use App\Events\StockUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('supplier', 'branch', 'creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();
        $variants = ProductVariant::with('product')->orderBy('sku')->get();

        return view('admin.purchase_orders.create', compact('suppliers', 'branches', 'variants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'expected_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $items = [];
        $subtotal = 0;

        $variantIds = collect($data['items'])->pluck('variant_id')->unique()->all();
        $variants = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

        foreach ($data['items'] as $item) {
            $variant = $variants[$item['variant_id']];
            $total = $item['quantity'] * $item['unit_cost'];
            $subtotal += $total;
            $items[] = [
                'product_variant_id' => $variant->id,
                'quantity_ordered' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'total' => $total,
            ];
        }

        $order = PurchaseOrder::create([
            'po_number' => 'PO-' . strtoupper(Str::random(8)),
            'supplier_id' => $data['supplier_id'],
            'branch_id' => $data['branch_id'],
            'created_by' => auth()->id(),
            'status' => PurchaseOrderStatus::Pending->value,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'notes' => $data['notes'],
            'expected_at' => $data['expected_at'],
        ]);

        $order->items()->createMany($items);

        return redirect()->route('admin.purchase-orders.show', $order)
            ->with('success', __('global.po_created'));
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'branch', 'creator', 'items.variant.product');

        return view('admin.purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrderStatus::Pending->value, PurchaseOrderStatus::Sent->value])) {
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', __('global.po_cannot_edit'));
        }

        $suppliers = Supplier::active()->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();
        $variants = ProductVariant::with('product')->orderBy('sku')->get();
        $purchaseOrder->load('items.variant');

        return view('admin.purchase_orders.edit', compact('purchaseOrder', 'suppliers', 'branches', 'variants'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrderStatus::Pending->value, PurchaseOrderStatus::Sent->value])) {
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', __('global.po_cannot_edit'));
        }

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'expected_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $items = [];
        $subtotal = 0;

        foreach ($data['items'] as $item) {
            $total = $item['quantity'] * $item['unit_cost'];
            $subtotal += $total;
            $items[] = [
                'product_variant_id' => $item['variant_id'],
                'quantity_ordered' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'total' => $total,
            ];
        }

        $purchaseOrder->update([
            'supplier_id' => $data['supplier_id'],
            'branch_id' => $data['branch_id'],
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'notes' => $data['notes'],
            'expected_at' => $data['expected_at'],
        ]);

        $purchaseOrder->items()->delete();
        $purchaseOrder->items()->createMany($items);

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', __('global.po_updated'));
    }

    public function markSent(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrderStatus::Pending->value) {
            return back()->with('error', __('global.po_invalid_status'));
        }

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Sent->value,
            'ordered_at' => now(),
        ]);

        return back()->with('success', __('global.po_marked_sent'));
    }

    public function receiveForm(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, [PurchaseOrderStatus::Received->value, PurchaseOrderStatus::Cancelled->value])) {
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', __('global.po_cannot_receive'));
        }

        $purchaseOrder->load('items.variant.product');

        return view('admin.purchase_orders.receive', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, [PurchaseOrderStatus::Received->value, PurchaseOrderStatus::Cancelled->value])) {
            return back()->with('error', __('global.po_cannot_receive'));
        }

        $data = $request->validate([
            'items' => 'required|array',
            'items.*.received' => 'required|integer|min:0',
        ]);

        $allReceived = true;
        $branchId = $purchaseOrder->branch_id ?? auth()->user()->branch_id ?? 1;
        $purchaseOrder->load('items.variant.branches');

        foreach ($purchaseOrder->items as $item) {
            $error = $this->processReceivedItem($item, $data, $branchId, $purchaseOrder);
            if ($error) {
                return $error;
            }

            if ($item->quantity_received < $item->quantity_ordered) {
                $allReceived = false;
            }
        }

        $newStatus = $allReceived ? PurchaseOrderStatus::Received->value : PurchaseOrderStatus::PartiallyReceived->value;
        $purchaseOrder->update([
            'status' => $newStatus,
            'received_at' => $allReceived ? now() : null,
        ]);

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', __('global.po_received'));
    }

    private function processReceivedItem($item, array $data, int $branchId, PurchaseOrder $purchaseOrder): ?\Illuminate\Http\RedirectResponse
    {
        $receivedQty = (int) ($data['items'][$item->id]['received'] ?? 0);
        if ($receivedQty <= 0) {
            return null;
        }

        $variant = $item->variant;
        $newReceived = $item->quantity_received + $receivedQty;

        if ($newReceived > $item->quantity_ordered) {
            return back()->with('error', __('global.po_receive_exceeds', [
                'variant' => $variant->sku,
            ]));
        }

        $item->update(['quantity_received' => $newReceived]);

        $branchPivot = $variant->branches->firstWhere('id', $branchId);
        if ($branchPivot) {
            $stockBefore = $branchPivot->pivot->stock;
            $stockAfter = $stockBefore + $receivedQty;
            $variant->branches()->updateExistingPivot($branchId, [
                'stock' => $stockAfter,
            ]);

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'branch_id' => $branchId,
                'type' => 'purchase_receive',
                'quantity' => $receivedQty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => PurchaseOrder::class,
                'reference_id' => $purchaseOrder->id,
            ]);

            StockUpdated::dispatch(
                variantId: $variant->id,
                productId: $variant->product_id,
                branchId: $branchId,
                stockBefore: $stockBefore,
                stockAfter: $stockAfter,
                action: 'purchase_receive',
            );
        } else {
            $variant->branches()->attach($branchId, ['stock' => $receivedQty]);
        }

        return null;
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, [PurchaseOrderStatus::Received->value, PurchaseOrderStatus::Cancelled->value])) {
            return back()->with('error', __('global.po_cannot_cancel'));
        }

        $purchaseOrder->update(['status' => PurchaseOrderStatus::Cancelled->value]);

        return back()->with('success', __('global.po_cancelled'));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === PurchaseOrderStatus::Received->value) {
            return back()->with('error', __('global.po_cannot_delete'));
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('admin.purchase-orders.index')
            ->with('success', __('global.po_deleted'));
    }
}
