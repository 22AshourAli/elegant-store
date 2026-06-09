<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function guard(): void
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, __('global.unauthorized'));
        }
    }

    public function index(Request $request)
    {
        $this->guard();

        $type = $request->query('type', 'staff');

        $query = User::with('branch');

        if ($type === 'customers') {
            $query->where('role', 'customer');
        } else {
            $query->where('role', '!=', 'customer');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $counts = [
            'staff' => User::where('role', '!=', 'customer')->count(),
            'customers' => User::where('role', 'customer')->count(),
        ];

        return view('admin.users.index', compact('users', 'type', 'counts'));
    }

    public function create(Request $request)
    {
        $this->guard();

        $type = $request->query('type', 'staff');

        if ($type === 'customers') {
            return view('admin.users.create', ['type' => 'customers']);
        }

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.create', compact('branches', 'type'));
    }

    public function store(Request $request)
    {
        $this->guard();

        $type = $request->input('user_type', 'staff');

        if ($type === 'customers') {
            $data = $request->validate([
                'email' => 'required|email|unique:users,email',
            ]);

            $rawPassword = Str::random(10);

            $user = User::create([
                'name' => strstr($data['email'], '@', true),
                'email' => $data['email'],
                'password' => bcrypt($rawPassword),
                'role' => 'customer',
                'branch_id' => null,
            ]);

            return redirect()->route('admin.users.index', ['type' => 'customers'])
                ->with('success', __('global.admin_customer_created', ['password' => $rawPassword, 'email' => $user->email]));
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['manager', 'super_admin'])],
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
        ]);

        $data['password'] = bcrypt($data['password']);
        $data['branch_id'] = $data['role'] === 'manager' ? $data['branch_id'] : null;

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', __('global.admin_user_created'));
    }

    public function edit(User $user)
    {
        $this->guard();

        if ($user->isCustomer()) {
            return view('admin.users.edit', ['user' => $user, 'type' => 'customers']);
        }

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.edit', ['user' => $user, 'branches' => $branches, 'type' => 'staff']);
    }

    public function update(Request $request, User $user)
    {
        $this->guard();

        if ($user->isCustomer()) {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:8',
            ]);

            if ($data['password']) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return redirect()->route('admin.users.index', ['type' => 'customers'])
                ->with('success', __('global.admin_user_updated'));
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::in(['manager', 'super_admin'])],
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($data['password']) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $data['branch_id'] = $data['role'] === 'manager' ? $data['branch_id'] : null;

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', __('global.admin_user_updated'));
    }

    public function destroy(User $user)
    {
        $this->guard();

        if ($user->isSuperAdmin()) {
            abort(403, __('global.unauthorized'));
        }

        $back = $user->isCustomer() ? ['type' => 'customers'] : [];
        $user->delete();

        return redirect()->route('admin.users.index', $back)
            ->with('success', __('global.admin_user_deleted'));
    }
}
