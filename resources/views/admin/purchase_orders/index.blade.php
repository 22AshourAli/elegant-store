@extends('admin.layouts.app')
@section('page-title', __('global.admin_purchase_orders'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_purchase_orders') }}</h2>
    <a href="{{ route('admin.purchase-orders.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_po') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_po_number') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_supplier') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_branch') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_total') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_date') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-3 font-mono text-xs">{{ $order->po_number }}</td>
                <td class="p-3">{{ $order->supplier->name ?? '-' }}</td>
                <td class="p-3">{{ $order->branch->name ?? '-' }}</td>
                <td class="p-3">{{ number_format($order->total, 2) }}</td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs @switch($order->status)
                        @case('pending') bg-yellow-100 text-yellow-800 @break
                        @case('sent') bg-blue-100 text-blue-800 @break
                        @case('partially_received') bg-orange-100 text-orange-800 @break
                        @case('received') bg-green-100 text-green-800 @break
                        @case('cancelled') bg-red-100 text-red-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch">
                        {{ __('global.po_status_' . $order->status) }}
                    </span>
                </td>
                <td class="p-3 text-xs">{{ $order->created_at->format('Y-m-d') }}</td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.purchase-orders.show', $order) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_view') }}</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-4 text-center text-gray-500">{{ __('global.admin_no_pos') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if(method_exists($orders, 'links'))
<div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection
