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
                                $variantImg = $item->variant->imageUrl()
                                    ?: $item->variant->product->firstImageUrl()
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
        <!-- Order Type Badge -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-bold {{ $order->order_type === 'offline' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    @if($order->order_type === 'offline')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @endif
                </svg>
                {{ $order->order_type === 'offline' ? __('global.admin_offline') : __('global.admin_online') }}
            </span>
        </div>

        <!-- Status change -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm" x-data="orderStatusUpdate()">
            <h2 class="text-lg font-bold mb-4 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.admin_update_status') }}</h2>

            <form @submit.prevent="updateStatus">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    @php
                        $key = 'orders.status_' . $order->status;
                        $trans = __($key);
                        $currentStatusLabel = $trans === $key ? ucfirst(str_replace('_', ' ', $order->status)) : $trans;
                    @endphp
                    <label class="block text-sm font-medium mb-2">{{ __('global.admin_current_status') }}
                        <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="statusLabel">{{ $currentStatusLabel }}</span>
                    </label>

                    <select name="status" x-model="selectedStatus" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 border p-2">
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

                <button type="submit" :disabled="saving" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span x-show="!saving">{{ __('global.admin_update_notify') }}</span>
                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-show="saving">{{ __('global.processing') }}...</span>
                </button>
            </form>

            <script>
                function orderStatusUpdate() {
                    return {
                        selectedStatus: '{{ $order->status }}',
                        statusLabel: '{{ $currentStatusLabel }}',
                        saving: false,
                        updateStatus() {
                            var val = this.selectedStatus;
                            if ((val === 'cancelled' || val === 'returned') && !confirm('{{ __("global.admin_confirm_cancel_msg") }} "' + this.statusLabel + '"؟ {{ __("global.admin_stock_will_restore") }}')) {
                                return;
                            }
                            this.saving = true;
                            fetch('{{ route('admin.orders.update-status', $order) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-HTTP-Method-Override': 'PATCH',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ status: this.selectedStatus })
                            })
                            .then(async res => {
                                const data = await res.json();
                                if (!res.ok) throw new Error(data.message || 'Error');
                                var statusKey = 'orders.status_' + data.status;
                                var locale = @json(app()->getLocale());
                                var labels = @json(collect(['pending','confirmed','processing','shipped','delivered','cancelled','returned'])->mapWithKeys(fn($s) => [$s => __("orders.status_" . $s)]));
                                var label = labels[data.status] || data.status.charAt(0).toUpperCase() + data.status.slice(1);
                                this.statusLabel = label;
                                this.saving = false;
                                window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                            })
                            .catch(err => {
                                this.saving = false;
                                window.dispatchEvent(new CustomEvent('toast', { detail: { message: err.message || '{{ __('global.error_occurred') }}', type: 'error' } }));
                            });
                        }
                    };
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
