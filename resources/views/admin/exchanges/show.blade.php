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
            <div class="bg-white dark:bg-gray-800 rounded shadow p-4 sm:p-6">
                <h3 class="font-bold mb-4">{{ __('global.exchange_details') }}</h3>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="w-full text-sm min-w-[400px]">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr><th class="p-2 text-right">{{ __('global.original_product') }}</th><th class="p-2 text-center">{{ __('global.quantity') }}</th><th class="p-2 text-right">{{ __('global.new_product') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($exchange->items as $item)
                                @php
                                    if (isset($item['order_item_id'])) {
                                        $orderItem = $exchange->order->items->find($item['order_item_id']);
                                    } elseif (isset($item['variant_id'])) {
                                        $orderItem = $exchange->order->items->first(fn($i) => $i->variant_id == $item['variant_id']);
                                    } else {
                                        $orderItem = null;
                                    }
                                    $newVariant = isset($item['new_variant_id'])
                                        ? \App\Models\ProductVariant::with('product')->find($item['new_variant_id'])
                                        : null;
                                @endphp
                                <tr class="border-t dark:border-gray-700">
                                    <td class="p-2 break-words">
                                        <div class="max-w-[180px] sm:max-w-xs">
                                            @if($orderItem)
                                                <span class="text-xs font-medium">{{ $orderItem->product_name }}</span>
                                                @if($orderItem->color) <span class="text-[10px] text-slate-500 whitespace-nowrap">({{ $orderItem->color }})</span> @endif
                                                @if($orderItem->size) <span class="text-[10px] text-slate-500 whitespace-nowrap">| {{ $orderItem->size }}</span> @endif
                                            @elseif(isset($item['variant_id']))
                                                @php $v = \App\Models\ProductVariant::with('product')->find($item['variant_id']); @endphp
                                                @if($v)
                                                    <span class="text-xs font-medium">{{ $v->product->name ?? '' }}</span>
                                                    @if($v->color) <span class="text-[10px] text-slate-500 whitespace-nowrap">({{ $v->color }})</span> @endif
                                                    @if($v->size) <span class="text-[10px] text-slate-500 whitespace-nowrap">| {{ $v->size }}</span> @endif
                                                @else
                                                    <span class="text-red-500 text-xs">{{ __('global.deleted') }}</span>
                                                @endif
                                            @else
                                                <span class="text-red-500 text-xs">{{ __('global.deleted') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-2 text-center whitespace-nowrap text-xs">{{ $item['quantity'] ?? '-' }}</td>
                                    <td class="p-2 break-words">
                                        <div class="max-w-[180px] sm:max-w-xs">
                                            @if($newVariant)
                                                <span class="text-xs font-medium">{{ $newVariant->product->name ?? '' }}</span>
                                                @if($newVariant->color) <span class="text-[10px] text-slate-500 whitespace-nowrap">({{ $newVariant->color }})</span> @endif
                                                @if($newVariant->size) <span class="text-[10px] text-slate-500 whitespace-nowrap">| {{ $newVariant->size }}</span> @endif
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded shadow p-4 sm:p-6">
            <h3 class="font-bold mb-4">{{ __('global.order_products') }}</h3>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm min-w-[400px]">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr><th class="p-2 text-right">{{ __('global.product') }}</th><th class="p-2 text-center">{{ __('global.quantity') }}</th><th class="p-2 text-center">{{ __('global.price') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach($exchange->order->items as $item)
                        <tr class="border-t dark:border-gray-700">
                            <td class="p-2 break-words">
                                <div class="max-w-[180px] sm:max-w-xs">
                                    <span class="text-xs font-medium">{{ $item->product_name }}</span>
                                    @if($item->color) <span class="text-[10px] text-slate-500 whitespace-nowrap">({{ $item->color }})</span> @endif
                                    @if($item->size) <span class="text-[10px] text-slate-500 whitespace-nowrap">| {{ $item->size }}</span> @endif
                                </div>
                            </td>
                            <td class="p-2 text-center whitespace-nowrap text-xs">{{ $item->quantity }}</td>
                            <td class="p-2 text-center whitespace-nowrap text-xs font-bold">{{ (int) round($item->total) }} {{ __('global.currency') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
