@extends('admin.layouts.app')
@section('page-title', __('global.po_receive') . ' - ' . $purchaseOrder->po_number)
@section('content')
<div class="mb-4">
    <h2 class="text-xl font-bold">{{ __('global.po_receive') . ' : ' . $purchaseOrder->po_number }}</h2>
    <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier->name }}</p>
</div>

<form action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}" method="POST">
    @csrf

    <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto mb-4">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                    <th class="p-3 text-right">{{ __('global.admin_product') }}</th>
                    <th class="p-3 text-right">{{ __('global.admin_ordered') }}</th>
                    <th class="p-3 text-right">{{ __('global.admin_already_received') }}</th>
                    <th class="p-3 text-right">{{ __('global.admin_qty_to_receive') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $item)
                <tr class="border-b dark:border-gray-700">
                    <td class="p-3">
                        {{ $item->variant->product->name ?? '-' }}
                        ({{ $item->variant->color }} / {{ $item->variant->size }})
                        <br><span class="font-mono text-xs text-gray-500">{{ $item->variant->sku }}</span>
                    </td>
                    <td class="p-3">{{ $item->quantity_ordered }}</td>
                    <td class="p-3">{{ $item->quantity_received }}</td>
                    <td class="p-3">
                        <input type="number" name="items[{{ $item->id }}][received]"
                               min="0" max="{{ $item->quantity_ordered - $item->quantity_received }}"
                               value="{{ $item->quantity_ordered - $item->quantity_received }}"
                               class="w-24 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">{{ __('global.po_confirm_receive') }}</button>
        <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}" class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-500">{{ __('global.admin_cancel') }}</a>
    </div>
</form>
@endsection
