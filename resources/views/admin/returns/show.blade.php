@extends('admin.layouts.app')
@section('page-title', $return->type === 'exchange' ? __('global.admin_exchange_request') . ' #' . $return->id : __('global.admin_return_request') . ' #' . $return->id)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
            <h3 class="font-bold mb-4">{{ __('global.admin_order_details') }}</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="font-semibold">{{ __('global.admin_type_colon') }}</span>
                    <span class="px-2 py-0.5 rounded text-xs font-bold {{ $return->type === 'exchange' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $return->type === 'exchange' ? __('global.admin_exchange') : __('global.admin_return') }}
                    </span>
                </div>
                <div><span class="font-semibold">{{ __('global.admin_customer_colon') }}</span> {{ $return->user->name }} ({{ $return->user->email }})</div>
                <div><span class="font-semibold">{{ __('global.admin_order_number_colon') }}</span> #{{ $return->order_id }}</div>
                <div><span class="font-semibold">{{ __('global.admin_status_colon') }}</span> {{ $return->status }}</div>
                <div><span class="font-semibold">{{ __('global.admin_order_date_colon') }}</span> {{ $return->created_at->format('Y-m-d H:i') }}</div>
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded">
                    <span class="font-semibold block mb-1">{{ __('global.admin_reason_colon') }}</span>
                    <p class="text-gray-600 dark:text-gray-300">{{ $return->reason }}</p>
                </div>
                @if($return->admin_note)
                    <div class="mt-4 p-4 bg-indigo-50 dark:bg-indigo-950/20 rounded">
                        <span class="font-semibold block mb-1">{{ __('global.admin_admin_note_colon') }}</span>
                        <p class="text-indigo-700 dark:text-indigo-300">{{ $return->admin_note }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($return->type === 'exchange' && $return->exchange_data)
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="font-bold mb-4">{{ __('global.admin_exchange_details') }}</h3>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr><th class="p-2 text-right">{{ __('global.admin_original_product') }}</th><th class="p-2">{{ __('global.admin_quantity') }}</th><th class="p-2">{{ __('global.admin_new_product') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach($return->exchange_data as $exchangeItem)
                            @php
                                $orderItem = $return->order->items->find($exchangeItem['order_item_id']);
                                $newVariant = \App\Models\ProductVariant::with('product')->find($exchangeItem['new_variant_id']);
                            @endphp
                            <tr class="border-t dark:border-gray-700 items-start">
                                <td class="p-2 break-words whitespace-normal">
                                    {{ $orderItem->product_name ?? '#' . $exchangeItem['order_item_id'] }}
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
                                        <span class="text-red-500">{{ __('global.admin_deleted') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
            <h3 class="font-bold mb-4">{{ __('global.admin_order_items') }}</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr><th class="p-2 text-right">{{ __('global.admin_product') }}</th><th class="p-2">{{ __('global.admin_quantity') }}</th><th class="p-2">{{ __('global.admin_price') }}</th></tr>
                </thead>
                <tbody>
                    @foreach($return->order->items as $item)
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
        @if($return->status === 'pending')
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6 space-y-4">
                <h3 class="font-bold">{{ __('global.admin_action') }}</h3>
                <form action="{{ route('admin.returns.approve', $return) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_note_optional') }}</label>
                        <textarea name="admin_note" rows="2" class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-2.5 rounded hover:bg-green-700 transition text-sm">
                        {{ $return->type === 'exchange' ? __('global.admin_approve_exchange') : __('global.admin_approve_return') }}
                    </button>
                </form>
                <form action="{{ route('admin.returns.reject', $return) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_rejection_reason') }} <span class="text-red-500">*</span></label>
                        <textarea name="admin_note" rows="2" required class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-red-600 text-white font-bold py-2.5 rounded hover:bg-red-700 transition text-sm">{{ __('global.admin_reject_request') }}</button>
                </form>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="font-bold mb-2">{{ __('global.admin_processed') }}</h3>
                <p class="text-sm text-gray-500">{{ __('global.admin_already_processed_desc') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
