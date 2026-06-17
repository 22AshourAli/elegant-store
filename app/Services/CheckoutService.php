<?php

namespace App\Services;

use App\Events\StockUpdated;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\ShippingService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        protected ShippingService $shippingService
    ) {}

    public function createOrder($user, $cartItems, $data)
    {
        return DB::transaction(function () use ($user, $cartItems, $data) {
            $subtotal = $data['subtotal'] ?? 0;
            $discount = $data['discount'] ?? 0;

            $previousOrders = $user->orders()->where('status', '!=', 'cancelled')->count();
            if ($previousOrders === 0) {
                $shipping = 0;
            } elseif (!empty($data['governorate_id'])) {
                $shippingResult = $this->shippingService->calculateCost(
                    governorateId: $data['governorate_id'],
                    cityId: $data['city_id'] ?? null,
                    cartTotal: $subtotal - $discount,
                );
                $shipping = $shippingResult['final_cost'];
            } else {
                $shipping = $data['shipping_cost'] ?? config('store.default_shipping', 30);
            }

            $total = $subtotal + $shipping - $discount;

            $orderData = [
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
            ];

            if (isset($data['governorate_id'])) {
                $orderData['governorate_id'] = $data['governorate_id'];
                $orderData['city_id'] = $data['city_id'] ?? null;
            }

            $order = Order::create($orderData);

            $branchId = $data['branch_id'] ?? 1;

            foreach ($cartItems as $variantId => $item) {
                $variant = ProductVariant::findOrFail($variantId);

                // Pessimistic lock on the pivot row to prevent race conditions
                $pivot = DB::table('branch_product_variant')
                    ->where('product_variant_id', $variantId)
                    ->where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->first();

                if (!$pivot || $pivot->stock < $item['quantity']) {
                    throw new \Exception("المخزون غير كافٍ للمنتج: {$item['product_name']}");
                }

                $order->items()->create([
                    'product_variant_id' => $variantId,
                    'product_name' => $item['product_name'],
                    'color' => $item['color'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);

                $stockAfter = $pivot->stock - $item['quantity'];
                DB::table('branch_product_variant')
                    ->where('product_variant_id', $variantId)
                    ->where('branch_id', $branchId)
                    ->update(['stock' => $stockAfter]);

                StockMovement::create([
                    'product_variant_id' => $variantId,
                    'branch_id' => $branchId,
                    'order_id' => $order->id,
                    'type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'stock_before' => $pivot->stock,
                    'stock_after' => $stockAfter,
                ]);

                StockUpdated::dispatch(
                    variantId: $variantId,
                    productId: $variant->product_id,
                    branchId: $branchId,
                    stockBefore: $pivot->stock,
                    stockAfter: $stockAfter,
                    action: 'sale',
                    orderId: $order->id,
                );
            }

            // Create initial payment record
            $order->payment()->create([
                'amount' => $total,
                'status' => 'pending',
                'gateway' => $data['payment_method'] === 'cash' ? 'cash' : 'paymob',
            ]);

            session()->forget('cart');

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
