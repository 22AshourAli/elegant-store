<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStateMachine;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function loginForm()
    {
        if (auth()->check() && auth()->user()->role === 'delivery') {
            return redirect()->route('delivery.orders');
        }
        return view('delivery.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
        }

        if (auth()->user()->role !== 'delivery') {
            auth()->logout();
            return back()->withErrors(['email' => 'ليس لديك صلاحية الدخول']);
        }

        $request->session()->regenerate();
        return redirect()->route('delivery.orders');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('delivery.login');
    }

    public function orders()
    {
        $user = auth()->user();

        $orders = Order::where('branch_id', $user->branch_id)
            ->whereIn('status', [
                OrderStatus::Shipped->value,
                OrderStatus::OutForDelivery->value,
            ])
            ->with('user')
            ->latest()
            ->paginate(20);

        $pendingCount = Order::where('branch_id', $user->branch_id)
            ->where('status', OrderStatus::Shipped->value)
            ->count();
        $activeCount = Order::where('branch_id', $user->branch_id)
            ->where('status', OrderStatus::OutForDelivery->value)
            ->count();

        return view('delivery.orders', compact('orders', 'pendingCount', 'activeCount'));
    }

    public function show(Order $order)
    {
        $user = auth()->user();

        if ($order->branch_id !== $user->branch_id) {
            abort(403);
        }

        $order->load('user', 'items.variant', 'payment');

        $fsm = app(OrderStateMachine::class);

        $deliveryTransitions = match ($order->status) {
            OrderStatus::Shipped->value => [OrderStatus::OutForDelivery->value],
            OrderStatus::OutForDelivery->value => [OrderStatus::Delivered->value],
            default => [],
        };

        $statusLabels = [];
        foreach ($deliveryTransitions as $status) {
            $statusLabels[$status] = OrderStateMachine::statusLabel($status, app()->getLocale());
        }

        return view('delivery.show', compact('order', 'statusLabels'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $user = auth()->user();

        if ($order->branch_id !== $user->branch_id) {
            abort(403);
        }

        $fsm = app(OrderStateMachine::class);

        $request->validate([
            'status' => 'required|in:' . implode(',', $fsm->availableTransitions($order)),
        ]);

        try {
            $fsm->transition($order, $request->status, $request->input('note'));
        } catch (\InvalidArgumentException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => __('global.order_status_updated'),
                'status' => $request->status,
            ]);
        }

        return back()->with('success', __('global.order_status_updated'));
    }
}
