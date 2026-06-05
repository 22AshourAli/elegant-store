<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Exchange;
use App\Models\Order;
use Illuminate\Http\Request;

class ExchangeRequestController extends Controller
{
    public function index()
    {
        $exchanges = auth()->user()->exchanges()
            ->with('order')
            ->latest()
            ->paginate(10);

        return view('shop.exchanges.index', compact('exchanges'));
    }

    public function create(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return back()->with('error', __('return.can_only_return_delivered'));
        }

        if ($order->delivered_at && $order->delivered_at->diffInDays(now()) > 3) {
            return back()->with('error', __('return.return_period_expired'));
        }

        if ($order->exchanges()
            ->whereIn('status', ['pending', 'approved'])
            ->exists()) {
            return back()->with('error', __('return.already_requested'));
        }

        $order->load('items.variant.product.variants.product');

        return view('shop.exchanges.create', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return back()->with('error', __('return.can_only_return_delivered'));
        }

        if ($order->delivered_at && $order->delivered_at->diffInDays(now()) > 3) {
            return back()->with('error', __('return.return_period_expired'));
        }

        if ($order->exchanges()
            ->whereIn('status', ['pending', 'approved'])
            ->exists()) {
            return back()->with('error', __('return.already_requested'));
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.new_variant_id' => 'required|exists:product_variants,id',
        ]);

        // Verify all items belong to this order
        $orderItemIds = $order->items->pluck('id')->toArray();
        foreach ($validated['items'] as $item) {
            if (!in_array($item['order_item_id'], $orderItemIds)) {
                return back()->with('error', 'Invalid order item selected.');
            }
        }

        $exchange = Exchange::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => 'pending',
            'reason' => $validated['reason'],
            'items' => $validated['items'],
        ]);

        // Notify all admins
        $admins = \App\Models\User::whereIn('role', ['super_admin', 'manager'])->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new \App\Notifications\ExchangeSubmitted($exchange));
            } catch (\Exception $e) {
                \Log::error('Exchange submission notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('exchanges.index')
            ->with('success', __('return.exchange_submitted'));
    }
}
