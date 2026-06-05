@extends('admin.layouts.app')
@section('page-title', $type === 'customers' ? __('global.admin_add_customer') : __('global.admin_add_user'))
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="user_type" value="{{ $type }}">

            @if($type === 'customers')
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-6 p-4 bg-indigo-50 dark:bg-indigo-950/30 rounded-lg border border-indigo-100 dark:border-indigo-900/50">
                    <svg class="w-8 h-8 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm text-indigo-700 dark:text-indigo-300">{{ __('global.admin_customer_create_hint') }}</p>
                </div>

                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.email') }} <span class="text-red-500">*</span></label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm"
                    placeholder="customer@example.com">
                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">{{ __('global.admin_customer_password_auto') }}</p>
            </div>
            @else
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_name') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name') }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm"
                    placeholder="{{ __('global.name_placeholder') }}">
                @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.email') }} <span class="text-red-500">*</span></label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm"
                    placeholder="{{ __('global.email_placeholder') }}">
                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.password') }} <span class="text-red-500">*</span></label>
                <input type="password" name="password" required
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm"
                    placeholder="{{ __('global.password_placeholder') }}">
                @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm"
                    placeholder="01000000000">
                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_role') }} <span class="text-red-500">*</span></label>
                <select name="role" id="role" required
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                    <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>{{ __('global.admin_manager') }}</option>
                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>{{ __('global.admin_super_admin') }}</option>
                </select>
                @error('role') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6" id="branchField">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('global.admin_branch') }}</label>
                <select name="branch_id"
                    class="w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white px-3 py-2.5 border text-sm">
                    <option value="">{{ __('global.admin_no_branch') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ __('global.admin_branch_hint') }}</p>
                @error('branch_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                    {{ $type === 'customers' ? __('global.admin_create_customer') : __('global.admin_save') }}
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
        toggleBranch();
    });
</script>
@endif
@endpush
@endsection
