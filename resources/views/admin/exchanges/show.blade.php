@extends('admin.layouts.app')
@section('page-title', __('global.exchange_request') . ' #' . $exchange->id)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
            <h3 class="font-bold mb-4">{{ __('global.order_details') }}</h3>
            <div class="space-y-3 text-sm">
                <div><span class="font-semibold">{{ __('global.customer') }}:</span> {{ $exchange->user->name }} ({{ $exchange->user->email }})</div>
                <div><span class="font-semibold">{{ __('global.order_number') }}:</span> #{{ $exchange->order_id }}</div>
                <div><span class="font-semibold">{{ __('global.status') }}:</span> {{ $exchange->status }}</div>
                <div><span class="font-semibold">{{ __('global.order_date') }}:</span> {{ $exchange->created_at->format('Y-m-d H:i') }}</div>
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded">
                    <span class="font-semibold block mb-1">{{ __('global.reason') }}:</span>
                    <p class="text-gray-600 dark:text-gray-300">{{ $exchange->reason }}</p>
                </div>
                @if($exchange->admin_note)
                    <div class="mt-4 p-4 bg-indigo-50 dark:bg-indigo-950/20 rounded">
                        <span class="font-semibold block mb-1">{{ __('global.admin_note') }}:</span>
                        <p class="text-indigo-700 dark:text-indigo-300">{{ $exchange->admin_note }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($exchange->items)
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="font-bold mb-4">{{ __('global.exchange_details') }}</h3>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr><th class="p-2 text-right">{{ __('global.original_product') }}</th><th class="p-2">{{ __('global.quantity') }}</th><th class="p-2">{{ __('global.new_product') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach($exchange->items as $item)
                            @php
                                $orderItem = $exchange->order->items->find($item['order_item_id']);
                                $newVariant = \App\Models\ProductVariant::with('product')->find($item['new_variant_id']);
                            @endphp
                            <tr class="border-t dark:border-gray-700 items-start">
                                <td class="p-2 break-words whitespace-normal">
                                    {{ $orderItem->product_name ?? '#' . $item['order_item_id'] }}
                                    @if($orderItem && $orderItem->color) <span class="whitespace-nowrap">({{ $orderItem->color }})</span> @endif
                                    @if($orderItem && $orderItem->size) <span class="whitespace-nowrap">| {{ $orderItem->size }}</span> @endif
                                </td>
                                <td class="p-2 whitespace-nowrap">{{ $orderItem->quantity ?? '-' }}</td>
                                <td class="p-2 break-words whitespace-normal">
                                    @if($newVariant)
                                        {{ $newVariant->product->name ?? '' }} #{{ $newVariant->id }}
                                        @if($newVariant->color) <span class="whitespace-nowrap">({{ $newVariant->color }})</span> @endif
                                        @if($newVariant->size) <span class="whitespace-nowrap">| {{ $newVariant->size }}</span> @endif
                                    @else
                                        <span class="text-red-500">{{ __('global.deleted') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
            <h3 class="font-bold mb-4">{{ __('global.order_products') }}</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr><th class="p-2 text-right">{{ __('global.product') }}</th><th class="p-2">{{ __('global.quantity') }}</th><th class="p-2">{{ __('global.price') }}</th></tr>
                </thead>
                <tbody>
                    @foreach($exchange->order->items as $item)
                    <tr class="border-t dark:border-gray-700 items-start">
                        <td class="p-2 break-words whitespace-normal">{{ $item->product_name }} @if($item->color) <span class="whitespace-nowrap">({{ $item->color }})</span> @endif @if($item->size) <span class="whitespace-nowrap">| {{ $item->size }}</span> @endif</td>
                        <td class="p-2 whitespace-nowrap">{{ $item->quantity }}</td>
                        <td class="p-2 whitespace-nowrap">{{ (int) round($item->total) }} {{ __('global.currency') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        @if($exchange->status === 'pending')
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6 space-y-4">
                <h3 class="font-bold">{{ __('global.action') }}</h3>
                <form action="{{ route('admin.exchanges.approve', $exchange) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">{{ __('global.note_optional') }}</label>
                        <textarea name="admin_note" rows="2" class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-2.5 rounded hover:bg-green-700 transition text-sm">
                        {{ __('global.approve_exchange') }}
                    </button>
                </form>
                <form action="{{ route('admin.exchanges.reject', $exchange) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">{{ __('global.rejection_reason') }} <span class="text-red-500">*</span></label>
                        <textarea name="admin_note" rows="2" required class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-red-600 text-white font-bold py-2.5 rounded hover:bg-red-700 transition text-sm">{{ __('global.reject_request') }}</button>
                </form>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="font-bold mb-2">{{ __('global.processed') }}</h3>
                <p class="text-sm text-gray-500">{{ __('global.request_already_processed') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
