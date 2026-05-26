@extends('admin.layouts.app')
@section('page-title', __('global.admin_manage_coupons'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_coupons') }}</h2>
    <a href="{{ route('admin.coupons.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_new') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_code') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_type_value') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_expiry') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_usage') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($coupons as $coupon)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-3 font-mono font-bold">{{ $coupon->code }}</td>
                <td class="p-3">
                    {{ $coupon->value }} {{ $coupon->type == 'percentage' ? '%' : __('global.currency') }}
                </td>
                <td class="p-3" dir="ltr">
                    {{ $coupon->valid_until ? $coupon->valid_until->format('Y-m-d H:i') : __('global.admin_no_notes') }}
                </td>
                <td class="p-3">
                    {{ $coupon->times_used }} / {{ $coupon->usage_limit ?: '∞' }}
                </td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs {{ $coupon->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $coupon->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_edit') }}</a>
                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1" onclick="return confirm('{{ __("global.confirm_delete") }}')">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-gray-500">{{ __('global.admin_no_coupons') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
