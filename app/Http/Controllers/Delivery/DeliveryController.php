<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('delivery.orders');
        }

        return view('delivery.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->role !== \App\Enums\UserRole::Delivery->value) {
                Auth::logout();
                return back()->withErrors(['email' => 'ليس لديك صلاحية الوصول لبوابة التوصيل.']);
            }

            $request->session()->regenerate();
            return redirect()->route('delivery.orders');
        }

        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('delivery.login');
    }

    public function orders(Request $request)
    {
        $user = Auth::user();

        $query = Order::with(['items.variant.product', 'governorate', 'city'])
            ->whereIn('status', [
                OrderStatus::Shipped->value,
                OrderStatus::OutForDelivery->value,
            ]);

        if (!$user->isSuperAdmin() && !$user->isManager()) {
            $query->where(function ($q) use ($user) {
                $q->where('delivery_person_id', $user->id)
                    ->orWhereNull('delivery_person_id');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return view('delivery.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.variant.product', 'governorate', 'city', 'user', 'deliveryPerson']);

        return view('delivery.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', [
                OrderStatus::OutForDelivery->value,
                OrderStatus::Delivered->value,
                OrderStatus::Returned->value,
            ]),
            'delivery_notes' => 'nullable|string|max:500',
        ]);

        $stateMachine = app(OrderStateMachine::class);

        if (!$stateMachine->canTransition($order, $request->status)) {
            return back()->withErrors(['status' => 'لا يمكن تغيير الحالة إلى هذه الحالة.']);
        }

        $order->delivery_person_id = Auth::id();

        if ($request->filled('delivery_notes')) {
            $order->delivery_notes = $request->delivery_notes;
        }

        $order->save();

        $stateMachine->transition($order, $request->status, $request->delivery_notes);

        if ($request->status === OrderStatus::Delivered->value) {
            event(new \App\Events\OrderDelivered($order));
        }

        $statusLabel = OrderStateMachine::statusLabel($request->status, app()->getLocale());

        return back()->with('success', "تم تحديث حالة الطلب #{$order->id} إلى: {$statusLabel}");
    }
}
