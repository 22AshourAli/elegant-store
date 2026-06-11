<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function createOrder($user, $cartItems, $data)
    {
        return DB::transaction(function () use ($user, $cartItems, $data) {
            // Prefer subtotal/discount passed from CartService (which handles coupons)
            $subtotal = $data['subtotal'] ?? 0;
            $discount = $data['discount'] ?? 0;

            // First-order free shipping: if this is the user's first completed order, shipping = 0
            $previousOrders = $user->orders()->where('status', '!=', 'cancelled')->count();
            if ($previousOrders === 0) {
                $shipping = 0;
            } else {
                // Use provided shipping_cost or default to a simple zone-based placeholder
                $shipping = $data['shipping_cost'] ?? config('store.default_shipping', 30);
            }

            $total = $subtotal + $shipping - $discount;

            $order = Order::create([
                'user_id' => $user->id,
                'branch_id' => $data['branch_id'] ?? 1,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shipping,
                'total' => $total,
                'shipping_address' => $data['shipping_address'],
                'phone' => $data['phone'] ?? null,
                'notes' => $data['notes'] ?? '',
            ]);

            foreach ($cartItems as $variantId => $item) {
                $variant = ProductVariant::findOrFail($variantId);
                $order->items()->create([
                    'product_variant_id' => $variantId,
                    'product_name' => $item['product_name'],
                    'color' => $item['color'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);

                // Deduct stock from the branch
                $branchId = $data['branch_id'] ?? 1;
                $pivot = $variant->branches()->where('branch_id', $branchId)->first();
                if ($pivot && $pivot->pivot->stock >= $item['quantity']) {
                    $variant->branches()->updateExistingPivot($branchId, [
                        'stock' => $pivot->pivot->stock - $item['quantity'],
                    ]);
                } else {
                    throw new \Exception("المخزون غير كافٍ للمنتج: {$item['product_name']}");
                }
            }

            // Create initial payment record
            $order->payment()->create([
                'amount' => $total,
                'status' => 'pending',
                'gateway' => $data['payment_method'] === 'cash' ? 'cash' : 'paymob',
            ]);

            // Clear session cart only for cash. Online payments will clear upon success.
            if ($data['payment_method'] === 'cash') {
                session()->forget('cart');
            }

            // Dispatch Notifications — each notification in its own try-catch so one failure never blocks others
            try {
                $locale = $user->locale ?? app()->getLocale();
                App::setLocale($locale);
                $user->notify(new \App\Notifications\OrderPlacedNotification($order));
            } catch (\Throwable $e) {
                \Log::error('Customer notif failed: ' . $e->getMessage());
            }

            $admins = \App\Models\User::whereIn('role', ['super_admin', 'manager'])->get();
            foreach ($admins as $admin) {
                try {
                    $adminLocale = $admin->locale ?? config('app.fallback_locale', 'ar');
                    App::setLocale($adminLocale);
                    $admin->notify(new \App\Notifications\NewOrderAdminNotification($order));
                } catch (\Throwable $e) {
                    \Log::error('Admin notif failed for ' . $admin->email . ': ' . $e->getMessage());
                }
            }

            return $order;
        });
    }
}
