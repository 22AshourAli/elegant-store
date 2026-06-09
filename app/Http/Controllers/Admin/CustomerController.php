<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = User::where('role', 'customer')
            ->withCount(['orders as orders_count'])
            ->withSum(['orders as total_spent'], 'total')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function show(User $user)
    {
        if ($user->role !== 'customer') {
            abort(404);
        }

        $orders = $user->orders()
            ->with('items', 'cashier')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($o) {
                return [
                    'id' => $o->id,
                    'total' => (float) $o->total,
                    'order_type' => $o->order_type,
                    'payment_method' => $o->payment_method,
                    'payment_status' => $o->payment_status,
                    'status' => $o->status,
                    'items_count' => $o->items->count(),
                    'cashier_name' => $o->cashier?->name,
                    'created_at' => $o->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'avatar' => $user->avatarUrl(),
            'created_at' => $user->created_at->format('Y-m-d'),
            'orders_count' => $user->orders()->count(),
            'total_spent' => (float) $user->orders()->sum('total'),
            'orders' => $orders,
        ]);
    }
}
