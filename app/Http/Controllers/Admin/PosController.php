<?php

namespace App\Http\Controllers\Admin;

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
            ->where('order_type', 'offline')
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
                'variants' => $p->variants->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'color' => $v->color,
                        'size' => $v->size,
                        'price' => (float) $v->current_price,
                        'sku' => $v->sku,
                        'stock' => (int) $v->total_stock,
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
        $request->validate([
            'payment_method' => 'required|in:cash,card,wallet',
            'name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = session('pos_cart', []);
        if (empty($cart)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('global.pos_cart_empty')], 422);
            }
            return back()->with('error', __('global.pos_cart_empty'));
        }

        $cartItems = $this->getEnrichedCart($cart);
        $total = $this->cartTotal($cart);
        $customerName = $request->input('name');
        $customerPhone = $request->input('phone');
        $paymentMethod = $request->input('payment_method');

        $branchId = auth()->user()->branch_id ?? 1;

        try {
            $order = DB::transaction(function () use ($cartItems, $total, $customerName, $customerPhone, $paymentMethod, $request, $branchId) {
                $userId = null;

                if ($customerPhone) {
                    $user = User::firstOrCreate(
                        ['phone' => $customerPhone],
                        [
                            'name' => $customerName ?: $customerPhone,
                            'password' => bcrypt('changeme'),
                            'role' => 'customer',
                        ]
                    );
                    $userId = $user->id;

                    if ($customerName && $user->name === $customerPhone) {
                        $user->update(['name' => $customerName]);
                    }
                }

                $order = Order::create([
                    'user_id' => $userId,
                    'cashier_id' => auth()->id(),
                    'branch_id' => $branchId,
                    'order_type' => 'offline',
                    'status' => 'confirmed',
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'paid',
                    'subtotal' => $total,
                    'discount' => 0,
                    'shipping_cost' => 0,
                    'total' => $total,
                    'phone' => $customerPhone,
                    'notes' => $request->input('notes', ''),
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

                    $pivot = $variant->branches()->where('branch_id', $branchId)->first();
                    if ($pivot && $pivot->pivot->stock >= $item['quantity']) {
                        $stockBefore = $pivot->pivot->stock;
                        $stockAfter = $stockBefore - $item['quantity'];
                        $variant->branches()->updateExistingPivot($branchId, [
                            'stock' => $stockAfter,
                        ]);

                        StockMovement::create([
                            'product_variant_id' => $variantId,
                            'branch_id' => $branchId,
                            'order_id' => $order->id,
                            'type' => 'sale',
                            'quantity' => -$item['quantity'],
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                        ]);

                        StockUpdated::dispatch(
                            variantId: $variantId,
                            productId: $variant->product_id,
                            branchId: $branchId,
                            stockBefore: $stockBefore,
                            stockAfter: $stockAfter,
                            action: 'sale',
                            orderId: $order->id,
                        );
                    } else {
                        throw new \Exception(__('global.pos_insufficient_stock', ['product' => $item['product_name']]));
                    }
                }

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
        $variants = ProductVariant::with('product')->whereIn('id', $ids)->get()->keyBy('id');

        $items = [];
        foreach ($cart as $variantId => $item) {
            $variant = $variants->get($variantId);
            if (!$variant) continue;

            $items[$variantId] = [
                'variant_id' => $variantId,
                'product_name' => $variant->product->name,
                'color' => $variant->color,
                'size' => $variant->size,
                'price' => (float) $variant->current_price,
                'quantity' => $item['quantity'],
                'total' => (float) $variant->current_price * $item['quantity'],
                'stock' => $variant->total_stock,
                'image' => $variant->imageUrl(),
            ];
        }

        return $items;
    }

    public function cartTotal($cart)
    {
        $items = $this->getEnrichedCart($cart);
        return array_sum(array_column($items, 'total'));
    }
}
