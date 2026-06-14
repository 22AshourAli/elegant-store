@extends('admin.layouts.app')
@section('page-title', __('global.admin_manage_shipping_rates'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_shipping_rates') }}</h2>
    <a href="{{ route('admin.shipping-rates.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_new') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_governorate') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_city') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_district') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_rate') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_min_cart') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_provider') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_estimated_days') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rates as $rate)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-3">{{ $rate->governorate?->name ?? '-' }}</td>
                <td class="p-3">{{ $rate->city?->name ?? __('global.all') }}</td>
                <td class="p-3">{{ $rate->district?->name ?? __('global.all') }}</td>
                <td class="p-3">{{ number_format($rate->rate, 2) }} {{ __('global.currency') }}</td>
                <td class="p-3">{{ $rate->min_cart_amount ? number_format($rate->min_cart_amount, 2) . ' ' . __('global.currency') : '-' }}</td>
                <td class="p-3">{{ $rate->shippingProvider?->name ?? '-' }}</td>
                <td class="p-3">{{ $rate->estimated_days ?? '-' }}</td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs {{ $rate->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $rate->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.shipping-rates.edit', $rate) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_edit') }}</a>
                    <form action="{{ route('admin.shipping-rates.destroy', $rate) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1" onclick="return confirm('{{ __("global.confirm_delete") }}')">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="p-4 text-center text-gray-500">{{ __('global.admin_no_shipping_rates') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($rates, 'links'))
<div class="mt-4">{{ $rates->links() }}</div>
@endif
@endsection
