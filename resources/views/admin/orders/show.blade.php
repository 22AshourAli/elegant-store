@extends('admin.layouts.app')

@section('page-title', __('global.order_details') . ' #' . $order->id)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold flex items-center gap-1">
            <span>&larr;</span> {{ __('global.back_to_orders') }}
        </a>
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            {{ __('global.invoice_print') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-6 shadow-sm border border-green-200">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-6 shadow-sm border border-red-200">
        {{ session('error') }}
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-8">
    <!-- Left side details (2 cols) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Products -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-lg font-bold mb-4 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.admin_products_ordered') }}</h2>
            <div class="divide-y dark:divide-gray-700">
                @foreach($order->items as $item)
                <div class="flex items-center py-4 first:pt-0 last:pb-0">
                            @php
                                $variantImg = $item->variant->getFirstMediaUrl('variant_images', 'thumb')
                                    ?: $item->variant->getFirstMediaUrl('variant_images')
                                    ?: $item->variant->product->getFirstMediaUrl('product_images', 'thumb')
                                    ?: $item->variant->product->getFirstMediaUrl('product_images')
                                    ?: asset('images/logo.svg');
                            @endphp
                            <img src="{{ $variantImg }}" loading="lazy" class="w-14 h-16 object-cover rounded-lg border dark:border-gray-700 flex-shrink-0 ml-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 dark:text-white truncate">{{ $item->product_name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($item->color) {{ __('global.color_label') }} {{ $item->color }} @endif
                            @if($item->color && $item->size) | @endif
                            @if($item->size) {{ __('global.size_label') }} {{ $item->size }} @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ __('global.qty_label') }} {{ $item->quantity }} × {{ (int) round($item->unit_price) }} {{ __('global.currency') }}</p>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white flex-shrink-0">{{ (int) round($item->total) }} {{ __('global.currency') }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Customer & Shipping details -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-lg font-bold mb-4 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.admin_shipping_info') }}</h2>

            <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300">
                <div>
                    <span class="block text-gray-400 text-xs">{{ __('global.admin_customer') }}:</span>
                    <span class="font-bold text-gray-900 dark:text-white text-base">{{ $order->user->name }}</span>
                    <span class="block text-gray-500 text-xs mt-0.5">{{ $order->user->email }}</span>
                </div>
                <div>
                    <span class="block text-gray-400 text-xs">{{ __('global.shipping_address_title') }}:</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $order->shipping_address }}</span>
                </div>
            </div>

            @if($order->notes)
                <div class="mt-4 pt-4 border-t dark:border-gray-700">
                    <span class="block text-gray-400 text-xs mb-1">{{ __('global.admin_customer_notes') }}</span>
                    <p class="text-sm italic dark:text-gray-400 bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">" {{ $order->notes }} "</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right side (Status and Payment updates) -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Status change -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-lg font-bold mb-4 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.admin_update_status') }}</h2>

            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" onsubmit="return confirmStatusChange(this)">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    @php
                        $key = 'orders.status_' . $order->status;
                        $trans = __($key);
                        $currentStatusLabel = $trans === $key ? ucfirst(str_replace('_', ' ', $order->status)) : $trans;
                    @endphp
                    <label class="block text-sm font-medium mb-2">{{ __('global.admin_current_status') }}
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $currentStatusLabel }}</span>
                    </label>

                    <select name="status" id="order-status-select" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 border p-2">
                        @foreach(['pending','confirmed','processing','shipped','delivered','cancelled','returned'] as $st)
                            @php
                                $k = 'orders.status_' . $st;
                                $t = __($k);
                                $label = $t === $k ? ucfirst(str_replace('_', ' ', $st)) : $t;
                            @endphp
                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
                    {{ __('global.admin_update_notify') }}
                </button>
            </form>

            <script>
                function confirmStatusChange(form) {
                    var select = form.querySelector('#order-status-select');
                    var val = select.value;
                    if (val === 'cancelled' || val === 'returned') {
                        return confirm('{{ __("global.admin_confirm_cancel_msg") }} "' + select.options[select.selectedIndex].text + '"؟ {{ __("global.admin_stock_will_restore") }}');
                    }
                    return true;
                }
            </script>
        </div>

        <!-- Payment details -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
            <h2 class="text-lg font-bold mb-4 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.admin_payment_details') }}</h2>

            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <div class="flex justify-between">
                    <span>{{ __('global.admin_payment_method') }}:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        @if($order->payment_method === 'cash')
                            {{ __('global.cash_on_delivery_status') }}
                        @elseif($order->payment_method === 'card')
                            {{ __('global.credit_card_status') }}
                        @else
                            {{ __('global.wallet_status') }}
                        @endif
                    </span>
                </div>
                <div class="flex justify-between pb-3 border-b dark:border-gray-700">
                    <span>{{ __('global.payment_status_label') }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                        @if($order->payment_status === 'paid') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                        @elseif($order->payment_status === 'unpaid') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                        @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @endif">
                        @if($order->payment_status === 'paid') {{ __('global.admin_paid') }}
                        @elseif($order->payment_status === 'unpaid') {{ __('global.admin_unpaid') }}
                        @else {{ __('global.admin_failed') }} @endif
                    </span>
                </div>

                <div class="flex justify-between text-xs text-gray-400">
                    <span>{{ __('global.admin_gateway_id') }}</span>
                    <span>{{ $order->payment->transaction_id ?? __('global.admin_no_notes') }}</span>
                </div>

                <div class="flex justify-between font-bold text-base text-gray-900 dark:text-white pt-2">
                    <span>{{ __('global.admin_total_amount') }}</span>
                    <span class="text-indigo-600 dark:text-indigo-400">{{ (int) round($order->total) }} {{ __('global.currency') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
