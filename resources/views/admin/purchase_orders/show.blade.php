@extends('admin.layouts.app')
@section('page-title', __('global.admin_po_detail') . ' - ' . $purchaseOrder->po_number)
@section('content')
<div class="mb-4 flex justify-between items-center">
    <div>
        <h2 class="text-xl font-bold">{{ $purchaseOrder->po_number }}</h2>
        <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier->name }}</p>
    </div>
    <div class="flex gap-2">
        @if(in_array($purchaseOrder->status, ['pending', 'sent']))
            <a href="{{ route('admin.purchase-orders.edit', $purchaseOrder) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">{{ __('global.admin_edit') }}</a>
        @endif
        @if($purchaseOrder->status === 'pending')
            <form action="{{ route('admin.purchase-orders.mark-sent', $purchaseOrder) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700">{{ __('global.po_mark_sent') }}</button>
            </form>
        @endif
        @if(in_array($purchaseOrder->status, ['pending', 'sent', 'partially_received']))
            <a href="{{ route('admin.purchase-orders.receive-form', $purchaseOrder) }}" class="bg-green-600 text-white px-3 py-1.5 rounded text-sm hover:bg-green-700">{{ __('global.po_receive') }}</a>
        @endif
        @if(!in_array($purchaseOrder->status, ['received', 'cancelled']))
            <form action="{{ route('admin.purchase-orders.cancel', $purchaseOrder) }}" method="POST" class="inline" onsubmit="return confirm('{{ __("global.confirm_cancel") }}')">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm hover:bg-red-700">{{ __('global.admin_cancel') }}</button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h3 class="font-bold mb-2">{{ __('global.admin_order_info') }}</h3>
        <table class="text-sm w-full">
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_status') }}</td><td><span class="px-2 py-0.5 rounded text-xs @switch($purchaseOrder->status) @case('pending') bg-yellow-100 text-yellow-800 @break @case('sent') bg-blue-100 text-blue-800 @break @case('partially_received') bg-orange-100 text-orange-800 @break @case('received') bg-green-100 text-green-800 @break @case('cancelled') bg-red-100 text-red-800 @break @default bg-gray-100 text-gray-800 @endswitch">{{ __('global.po_status_' . $purchaseOrder->status) }}</span></td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_supplier') }}</td><td>{{ $purchaseOrder->supplier->name }}</td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_branch') }}</td><td>{{ $purchaseOrder->branch->name ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_created_by') }}</td><td>{{ $purchaseOrder->creator->name ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_expected_at') }}</td><td>{{ $purchaseOrder->expected_at ? $purchaseOrder->expected_at->format('Y-m-d') : '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_received_at') }}</td><td>{{ $purchaseOrder->received_at ? $purchaseOrder->received_at->format('Y-m-d H:i') : '-' }}</td></tr>
        </table>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h3 class="font-bold mb-2">{{ __('global.admin_totals') }}</h3>
        <table class="text-sm w-full">
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_subtotal') }}</td><td>{{ number_format($purchaseOrder->subtotal, 2) }}</td></tr>
            <tr><td class="py-1 text-gray-500 font-bold">{{ __('global.admin_total') }}</td><td class="font-bold">{{ number_format($purchaseOrder->total, 2) }}</td></tr>
        </table>
        @if($purchaseOrder->notes)
            <div class="mt-3">
                <p class="text-gray-500 text-xs">{{ __('global.admin_notes') }}</p>
                <p class="text-sm">{{ $purchaseOrder->notes }}</p>
            </div>
        @endif
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_product') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_sku') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_qty_ordered') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_qty_received') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_unit_cost') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $item)
            <tr class="border-b dark:border-gray-700">
                <td class="p-3">{{ $item->variant->product->name ?? '-' }} ({{ $item->variant->color }} / {{ $item->variant->size }})</td>
                <td class="p-3 font-mono text-xs">{{ $item->variant->sku }}</td>
                <td class="p-3">{{ $item->quantity_ordered }}</td>
                <td class="p-3">{{ $item->quantity_received }}</td>
                <td class="p-3">{{ number_format($item->unit_cost, 2) }}</td>
                <td class="p-3">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    <a href="{{ route('admin.purchase-orders.index') }}" class="text-indigo-600 hover:underline">&larr; {{ __('global.admin_back_to_list') }}</a>
</div>
@endsection
