<?php

namespace App\Http\Controllers\Shop;

use App\Enums\ExchangeStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Exchange;
use App\Models\Order;
use Illuminate\Http\Request;

class ExchangeRequestController extends Controller
{
    public function index()
    {
        $exchanges = auth()->user()->exchanges()->with('order')->latest()->paginate(10);
        return view('shop.exchanges.index', compact('exchanges'));
    }

    public function create(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->isWithinReturnWindow()) {
            return back()->with('error', __('return.return_period_expired'));
        }

        if ($order->exchanges()
            ->whereIn('status', [ExchangeStatus::Pending->value, ExchangeStatus::Approved->value])
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

        if (!$order->isWithinReturnWindow()) {
            return back()->with('error', __('return.return_period_expired'));
        }

        if ($order->exchanges()
            ->whereIn('status', [ExchangeStatus::Pending->value, ExchangeStatus::Approved->value])
            ->exists()) {
            return back()->with('error', __('return.already_requested'));
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.new_variant_id' => 'required|exists:product_variants,id',
        ]);

        $orderItemIds = $order->items->pluck('id')->toArray();
        foreach ($validated['items'] as $item) {
            if (!in_array($item['order_item_id'], $orderItemIds)) {
                return back()->with('error', __('global.exchange_invalid_order_item'));
            }
        }

        $exchange = Exchange::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => ExchangeStatus::Pending->value,
            'reason' => $validated['reason'],
            'items' => $validated['items'],
        ]);

        $admins = \App\Models\User::whereIn('role', array_map(fn($r) => $r->value, UserRole::adminRoles()))->get();
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
