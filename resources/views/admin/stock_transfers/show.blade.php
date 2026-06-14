@extends('admin.layouts.app')
@section('page-title', __('global.admin_transfer_detail') . ' - ' . $stockTransfer->reference_number)
@section('content')
<div class="mb-4 flex justify-between items-center">
    <div>
        <h2 class="text-xl font-bold">{{ $stockTransfer->reference_number }}</h2>
        <p class="text-sm text-gray-500">{{ $stockTransfer->fromBranch->name }} &rarr; {{ $stockTransfer->toBranch->name }}</p>
    </div>
    <div class="flex gap-2">
        @if($stockTransfer->status === 'pending')
            <form action="{{ route('admin.stock-transfers.complete', $stockTransfer) }}" method="POST" class="inline" onsubmit="return confirm('{{ __("global.confirm_transfer") }}')">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-3 py-1.5 rounded text-sm hover:bg-green-700">{{ __('global.st_complete') }}</button>
            </form>
            <form action="{{ route('admin.stock-transfers.cancel', $stockTransfer) }}" method="POST" class="inline" onsubmit="return confirm('{{ __("global.confirm_cancel") }}')">
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
        <h3 class="font-bold mb-2">{{ __('global.admin_transfer_info') }}</h3>
        <table class="text-sm w-full">
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_status') }}</td><td>
                <span class="px-2 py-0.5 rounded text-xs @switch($stockTransfer->status)
                    @case('pending') bg-yellow-100 text-yellow-800 @break
                    @case('completed') bg-green-100 text-green-800 @break
                    @case('cancelled') bg-red-100 text-red-800 @break
                    @default bg-gray-100 text-gray-800
                @endswitch">{{ __('global.st_status_' . $stockTransfer->status) }}</span>
            </td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_from_branch') }}</td><td>{{ $stockTransfer->fromBranch->name }}</td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_to_branch') }}</td><td>{{ $stockTransfer->toBranch->name }}</td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_created_by') }}</td><td>{{ $stockTransfer->creator->name ?? '-' }}</td></tr>
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_created_at') }}</td><td>{{ $stockTransfer->created_at->format('Y-m-d H:i') }}</td></tr>
            @if($stockTransfer->completed_at)
            <tr><td class="py-1 text-gray-500">{{ __('global.admin_completed_at') }}</td><td>{{ $stockTransfer->completed_at->format('Y-m-d H:i') }}</td></tr>
            @endif
        </table>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h3 class="font-bold mb-2">{{ __('global.admin_notes') }}</h3>
        <p class="text-sm">{{ $stockTransfer->notes ?? '-' }}</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_product') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_sku') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_quantity') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockTransfer->items as $item)
            <tr class="border-b dark:border-gray-700">
                <td class="p-3">{{ $item->variant->product->name ?? '-' }} ({{ $item->variant->color }} / {{ $item->variant->size }})</td>
                <td class="p-3 font-mono text-xs">{{ $item->variant->sku }}</td>
                <td class="p-3">{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    <a href="{{ route('admin.stock-transfers.index') }}" class="text-indigo-600 hover:underline">&larr; {{ __('global.admin_back_to_list') }}</a>
</div>
@endsection
