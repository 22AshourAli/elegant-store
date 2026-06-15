@extends('admin.layouts.app')
@section('page-title', __('global.admin_shipping_rates'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_shipping_rates') }}</h2>
    <a href="{{ route('admin.shipping-rates.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_new') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow">
    <table class="w-full text-sm">
        <thead class="hidden md:table-header-group">
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.governorate') }}</th>
                <th class="p-3 text-right">{{ __('global.city') }}</th>
                <th class="p-3 text-right">{{ __('global.rate') }}</th>
                <th class="p-3 text-right">{{ __('global.min_cart_amount') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rates as $rate)
            <tr class="block md:table-row border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="block md:table-cell p-3"><span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.governorate') }}</span>{{ $rate->governorate->name ?? '-' }}</td>
                <td class="block md:table-cell p-3"><span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.city') }}</span>{{ $rate->city->name ?? __('global.all_cities') }}</td>
                <td class="block md:table-cell p-3 font-semibold"><span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.rate') }}</span>{{ (int) $rate->rate }} {{ __('global.currency') }}</td>
                <td class="block md:table-cell p-3"><span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.min_cart_amount') }}</span>{{ $rate->min_cart_amount ? (int) $rate->min_cart_amount . ' ' . __('global.currency') : '-' }}</td>
                <td class="block md:table-cell p-3">
                    <span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.admin_status') }}</span>
                    <span class="px-2 py-1 rounded text-xs {{ $rate->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $rate->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="block md:table-cell p-3 text-right md:text-center">
                    <span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.admin_actions') }}</span>
                    <a href="{{ route('admin.shipping-rates.edit', $rate) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_edit') }}</a>
                    <form action="{{ route('admin.shipping-rates.destroy', $rate) }}" method="POST" class="inline" onsubmit="return confirm('{{ __("global.confirm_delete") }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-gray-500">{{ __('global.admin_no_rates') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($rates, 'links'))
<div class="mt-4">{{ $rates->onEachSide(1)->links('vendor.pagination.admin') }}</div>
@endif
@endsection
