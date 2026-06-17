<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Events\StockUpdated;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $cart = session('pos_cart', []);
        $cartItems = $this->getEnrichedCart($cart);

        return view('admin.pos.index');
    }

    public function categories()
    {
        return response()->json(Category::select('id', 'name')->where('is_active', true)->get());
    }

    public function recentOrders()
    {
        $orders = Order::with('user', 'cashier')
            ->where('order_type', OrderType::Offline->value)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($o) {
                return [
                    'id' => $o->id,
                    'total' => (float) $o->total,
                    'customer_name' => $o->user?->name ?? $o->phone,
                    'cashier_name' => $o->cashier?->name,
                    'order_type' => $o->order_type,
                    'created_at' => $o->created_at->format('H:i - d/m'),
                ];
            });

        return response()->json($orders);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $limit = (int) $request->input('limit', 20);

        $query = Product::with(['variants.branches', 'media'])
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhereHas('variants', fn($q) => $q->where('sku', 'LIKE', "%{$search}%"));
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->limit(min($limit, 100))->get();

        return response()->json($products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->firstImageUrl(),
                'base_price' => (float) $p->base_price,
                'current_price' => (float) $p->current_price,
                'has_variants' => $p->has_variants,
                'is_on_sale' => $p->is_on_sale,
                'category_id' => $p->category_id,
                'variants' => $p->variants->map(function ($v) use ($p) {
                    return [
                        'id' => $v->id,
                        'color' => $v->color,
                        'size' => $v->size,
                        'price' => (float) $v->current_price,
                        'sku' => $v->sku,
                        'stock' => (int) $v->total_stock,
                        'image' => $v->imageUrl() ?: $p->firstImageUrl(),
                    ];
                }),
            ];
        }));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::with('branches')->findOrFail($request->variant_id);
        $qty = (int) $request->quantity;

        if ($variant->total_stock < $qty) {
            return response()->json(['error' => 'المخزون غير كافٍ'], 422);
        }

        $cart = session('pos_cart', []);

        if (isset($cart[$variant->id])) {
            $newQty = $cart[$variant->id]['quantity'] + $qty;
            if ($newQty > $variant->total_stock) {
                return response()->json(['error' => 'المخزون غير كافٍ'], 422);
            }
            $cart[$variant->id]['quantity'] = $newQty;
        } else {
            $cart[$variant->id] = [
                'variant_id' => $variant->id,
                'quantity' => $qty,
            ];
        }

        session(['pos_cart' => $cart]);

        return response()->json([
            'cart' => $this->getEnrichedCart($cart),
            'total' => $this->cartTotal($cart),
        ]);
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = session('pos_cart', []);

        if ((int) $request->quantity === 0) {
            unset($cart[$request->variant_id]);
        } else {
            $variant = ProductVariant::findOrFail($request->variant_id);
            if ((int) $request->quantity > $variant->total_stock) {
                return response()->json(['error' => 'المخزون غير كافٍ'], 422);
            }
            $cart[$request->variant_id]['quantity'] = (int) $request->quantity;
        }

        session(['pos_cart' => $cart]);

        return response()->json([
            'cart' => $this->getEnrichedCart($cart),
            'total' => $this->cartTotal($cart),
        ]);
    }

    public function removeFromCart($variantId)
    {
        $cart = session('pos_cart', []);
        unset($cart[$variantId]);
        session(['pos_cart' => $cart]);

        return response()->json([
            'cart' => $this->getEnrichedCart($cart),
            'total' => $this->cartTotal($cart),
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,wallet',
            'name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = session('pos_cart', []);
        if (empty($cart)) {
            return $request->expectsJson()
                ? response()->json(['error' => __('global.pos_cart_empty')], 422)
                : back()->with('error', __('global.pos_cart_empty'));
        }

        $cartItems = $this->getEnrichedCart($cart);
        $total = $this->cartTotal($cart);
        $branchId = auth()->user()->branch_id ?? 1;

        try {
            $order = DB::transaction(function () use ($cartItems, $total, $validated, $branchId) {
                $customerPhone = $validated['phone'] ?? null;
                $customerName = $validated['name'] ?? null;

                $userId = $this->resolvePosCustomer($customerPhone, $customerName);

                $order = Order::create([
                    'user_id' => $userId,
                    'cashier_id' => auth()->id(),
                    'branch_id' => $branchId,
                    'order_type' => OrderType::Offline->value,
                    'status' => OrderStatus::Confirmed->value,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => PaymentStatus::Paid->value,
                    'subtotal' => $total,
                    'discount' => 0,
                    'shipping_cost' => 0,
                    'total' => $total,
                    'phone' => $customerPhone,
                    'notes' => $validated['notes'] ?? '',
                ]);

                $this->processPosItems($order, $cartItems, $branchId);

                return $order;
            });

            session()->forget('pos_cart');

            $receipt = [
                'order_id' => $order->id,
                'date' => $order->created_at->format('Y-m-d H:i'),
                'cashier' => auth()->user()->name,
                'customer' => $customerName ?: ($customerPhone ?: null),
                'items' => $cartItems,
                'total' => $total,
                'payment_method' => __('global.' . $paymentMethod),
                'paid_at' => now()->format('H:i'),
                'store_name' => config('app.name'),
                'store_address' => '',
                'store_phone' => '',
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('global.pos_checkout_success'),
                    'receipt' => $receipt,
                    'order_id' => $order->id,
                ]);
            }

            return redirect()->route('admin.pos.index')
                ->with('success', __('global.pos_checkout_success'))
                ->with('lastOrder', $order->id);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function cart()
    {
        $cart = session('pos_cart', []);

        return response()->json([
            'cart' => $this->getEnrichedCart($cart),
            'total' => $this->cartTotal($cart),
        ]);
    }

    public function clearCart()
    {
        session()->forget('pos_cart');

        if (request()->expectsJson()) {
            return response()->json(['message' => 'تم تفريغ السلة']);
        }

        return redirect()->route('admin.pos.index');
    }

    public function getEnrichedCart($cart)
    {
        if (empty($cart)) return [];

        $ids = array_keys($cart);
        $variants = ProductVariant::with('product', 'product.media', 'media')->whereIn('id', $ids)->get()->keyBy('id');

        $items = [];
        foreach ($cart as $variantId => $item) {
            $variant = $variants->get($variantId);
            if (!$variant) continue;

            $image = $variant->imageUrl();
            if (!$image && $variant->color) {
                $sibling = $variant->product->variants
                    ->where('color', $variant->color)
                    ->first(fn($v) => $v->image_url || $v->hasMedia('variant_images'));
                $image = $sibling ? $sibling->imageUrl() : null;
            }

            $items[$variantId] = [
                'variant_id' => $variantId,
                'product_name' => $variant->product->name,
                'color' => $variant->color,
                'size' => $variant->size,
                'price' => (float) $variant->current_price,
                'quantity' => $item['quantity'],
                'total' => (float) $variant->current_price * $item['quantity'],
                'stock' => $variant->total_stock,
                'image' => $image ?: $variant->product->firstImageUrl(),
            ];
        }

        return $items;
    }

    public function cartTotal($cart)
    {
        $items = $this->getEnrichedCart($cart);
        return array_sum(array_column($items, 'total'));
    }

    private function resolvePosCustomer(?string $phone, ?string $name): ?int
    {
        if (!$phone) {
            return null;
        }

        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $name ?: $phone,
                'password' => bcrypt('changeme'),
                'role' => UserRole::Customer->value,
            ]
        );

        if ($name && $user->name === $phone) {
            $user->update(['name' => $name]);
        }

        return $user->id;
    }

    private function processPosItems(Order $order, array $cartItems, int $branchId): void
    {
        $variantIds = array_keys($cartItems);

        $variants = ProductVariant::with('product')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $branchPivot = DB::table('branch_product_variant')
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

            $pivot = $branchPivot->get($variantId);
            $stockBefore = $pivot ? $pivot->stock : 0;

            if ($stockBefore < $item['quantity']) {
                throw new \Exception(__('global.pos_insufficient_stock', ['product' => $item['product_name']]));
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

            $stockAfter = $stockBefore - $item['quantity'];

            $stockUpdates[] = [
                'product_variant_id' => $variantId,
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

        $this->applyStockUpdates($stockUpdates, $branchId);
        $this->dispatchStockEvents($stockMovements, $variants, $branchId, $order);
    }

    private function applyStockUpdates(array $stockUpdates, int $branchId): void
    {
        foreach ($stockUpdates as $update) {
            DB::table('branch_product_variant')
                ->where('product_variant_id', $update['product_variant_id'])
                ->where('branch_id', $branchId)
                ->update(['stock' => $update['stock']]);
        }
    }

    private function dispatchStockEvents(array $stockMovements, $variants, int $branchId, Order $order): void
    {
        if (empty($stockMovements)) {
            return;
        }

        StockMovement::insert($stockMovements);

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
}
