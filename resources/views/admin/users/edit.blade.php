@extends('admin.layouts.app')
@section('page-title', $type === 'customers' ? __('global.admin_edit_customer') : __('global.admin_edit_user'))
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_name') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.email') }} <span class="text-red-500">*</span></label>
                <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            @if($type !== 'customers')
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_role') }} <span class="text-red-500">*</span></label>
                <select name="role" id="role" required
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                    <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>{{ __('global.admin_manager') }}</option>
                    <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>{{ __('global.admin_super_admin') }}</option>
                </select>
                @error('role') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6" id="branchField" {{ $user->isManager() ? '' : 'style=display:none' }}>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_branch') }}</label>
                <select name="branch_id"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                    <option value="">{{ __('global.admin_no_branch') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ __('global.admin_branch_hint') }}</p>
                @error('branch_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            @endif

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.password') }}</label>
                <input type="password" name="password"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm"
                    placeholder="{{ __('global.admin_password_hint') }}">
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ __('global.admin_password_hint') }}</p>
                @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                    {{ __('global.admin_update') }}
                </button>
                <a href="{{ route('admin.users.index', ['type' => $type]) }}" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    {{ __('global.admin_cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
@if($type !== 'customers')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const role = document.getElementById('role');
        const branchField = document.getElementById('branchField');
        function toggleBranch() {
            branchField.style.display = role.value === 'manager' ? 'block' : 'none';
        }
        role.addEventListener('change', toggleBranch);
    });
</script>
@endif
@endpush
@endsection
