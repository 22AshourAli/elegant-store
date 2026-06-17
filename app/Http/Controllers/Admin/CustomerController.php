<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CursorService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $result = CursorService::applyCursor(
            User::where('role', UserRole::Customer->value)
                ->withCount(['orders as orders_count'])
                ->withSum(['orders as total_spent'], 'total')
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                    });
                }),
            $request->get('cursor'),
            'created_at',
            'desc',
            20
        );
        $customers = $result['data'];

        return view('admin.customers.index', compact('customers', 'search', 'result'));
    }

    public function show(User $user)
    {
        if ($user->role !== UserRole::Customer->value) {
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
