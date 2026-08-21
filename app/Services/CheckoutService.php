<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentGateway;
use App\Enums\StockMovementType;
use App\Enums\UserRole;
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

            $shipping = $this->resolveShippingCost($user, $data, $subtotal - $discount);
            $total = $subtotal + $shipping - $discount;

            $order = $this->createOrderRecord($user, $data, $subtotal, $discount, $shipping, $total);
            $branchId = $data['branch_id'] ?? 1;

            $this->processOrderItems($order, $cartItems, $branchId);

            $this->createPaymentRecord($order, $total, $data['payment_method']);

            $this->notifyCustomer($order, $user);
            $this->notifyAdmins($order);

            return $order;
        });
    }

    private function resolveShippingCost($user, array $data, float $cartTotal): float
    {
        $previousOrders = $user->orders()->where('status', '!=', OrderStatus::Cancelled->value)->count();
        if ($previousOrders === 0) {
            return 0;
        }
        if (!empty($data['governorate_id'])) {
            $result = $this->shippingService->calculateCost(
                governorateId: $data['governorate_id'],
                cityId: $data['city_id'] ?? null,
                cartTotal: $cartTotal,
            );
            return $result['final_cost'];
        }
        return $data['shipping_cost'] ?? config('store.default_shipping', 30);
    }

    private function createOrderRecord($user, array $data, float $subtotal, float $discount, float $shipping, float $total): Order
    {
        $orderData = [
            'user_id' => $user->id,
            'branch_id' => $data['branch_id'] ?? 1,
            'status' => OrderStatus::Pending->value,
            'payment_method' => $data['payment_method'],
            'payment_status' => PaymentStatus::Unpaid->value,
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

        return Order::create($orderData);
    }

    private function createPaymentRecord(Order $order, float $total, string $paymentMethod): void
    {
        $order->payment()->create([
            'amount' => $total,
            'status' => PaymentStatus::Unpaid->value,
            'gateway' => $paymentMethod === 'cash' ? PaymentGateway::Cash->value : PaymentGateway::Paymob->value,
        ]);
    }

    private function processOrderItems(Order $order, array $cartItems, int $branchId): void
    {
        // Batch 1: Load all variants with their product in one query
        $variantIds = array_keys($cartItems);
        $variants = ProductVariant::with('product')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        // Batch 2: Pessimistic-lock all pivot rows in one query
        $pivots = DB::table('branch_product_variant')
            ->whereIn('product_variant_id', $variantIds)
            ->where('branch_id', $branchId)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_variant_id');

        $now = now();
        $stockMovements = [];
        $stockUpdates = [];

        foreach ($cartItems as $variantId => $item) {
            $variant = $variants->get($variantId);
            if (!$variant) {
                throw new \Exception("المنتج غير موجود: {$item['product_name']}");
            }

            $pivot = $pivots->get($variantId);
            $stockBefore = $pivot ? $pivot->stock : 0;

            if ($stockBefore < $item['quantity']) {
                throw new \Exception("المخزون غير كافٍ للمنتج: {$item['product_name']}");
            }

            // Create order item
            $order->items()->create([
                'product_variant_id' => $variantId,
                'product_name' => $item['product_name'],
                'color' => $item['color'],
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);

            $stockAfter = $stockBefore - $item['quantity'];

            $stockUpdates[] = [
                'product_variant_id' => $variantId,
                'branch_id' => $branchId,
                'stock' => $stockAfter,
            ];

            $stockMovements[] = [
                'product_variant_id' => $variantId,
                'branch_id' => $branchId,
                'order_id' => $order->id,
                'type' => StockMovementType::Sale->value,
                'quantity' => -$item['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Batch 3: Bulk update stock rows
        foreach ($stockUpdates as $update) {
            DB::table('branch_product_variant')
                ->where('product_variant_id', $update['product_variant_id'])
                ->where('branch_id', $update['branch_id'])
                ->update(['stock' => $update['stock']]);
        }

        // Batch 4: Bulk insert stock movements
        if (!empty($stockMovements)) {
            StockMovement::insert($stockMovements);
        }

        // Batch 5: Dispatch events
        foreach ($stockMovements as $movement) {
            $variant = $variants->get($movement['product_variant_id']);
            StockUpdated::dispatch(
                variantId: $movement['product_variant_id'],
                productId: $variant->product_id,
                branchId: $branchId,
                stockBefore: $movement['stock_before'],
                stockAfter: $movement['stock_after'],
                action: StockMovementType::Sale->value,
                orderId: $order->id,
            );
        }
    }

    private function notifyCustomer(Order $order, $user): void
    {
        try {
            $locale = $user->locale ?? app()->getLocale();
            App::setLocale($locale);
            $user->notify(new \App\Notifications\OrderPlacedNotification($order));
        } catch (\Throwable $e) {
            \Log::error('Customer notif failed: ' . $e->getMessage());
        }
    }

    private function notifyAdmins(Order $order): void
    {
        $admins = \App\Models\User::whereIn('role', array_map(fn($r) => $r->value, UserRole::adminRoles()))->get();

        foreach ($admins as $admin) {
            try {
                $adminLocale = $admin->locale ?? config('app.fallback_locale', 'ar');
                App::setLocale($adminLocale);
                $admin->notify(new \App\Notifications\NewOrderAdminNotification($order));
            } catch (\Throwable $e) {
                \Log::error('Admin notif failed for ' . $admin->email . ': ' . $e->getMessage());
            }
        }
    }
}
