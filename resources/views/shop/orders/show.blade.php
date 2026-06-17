@extends('layouts.store')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ __('global.order_details') }} #{{ $order->id }}</h1>
            <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold flex items-center gap-1">
                <span>&rarr;</span> {{ __('global.back_to_orders') }}
            </a>
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

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Order Details (Left 2 cols) -->
            <div class="md:col-span-2 space-y-6">
                <!-- Products Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h2 class="text-lg font-bold mb-4 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.order_items') }}</h2>
                    <div class="divide-y dark:divide-gray-700">
                        @foreach($order->items as $item)
                        <div class="flex items-start py-4 first:pt-0 last:pb-0 gap-3 sm:gap-4">
                            @php
                                $v = $item->variant;
                                $variantImg = $v->imageUrl();
                                if (!$variantImg && $v->color) {
                                    $sibling = $v->product->variants
                                        ->where('color', $v->color)
                                        ->first(fn($sv) => $sv->image_url || $sv->hasMedia('variant_images'));
                                    $variantImg = $sibling ? $sibling->imageUrl() : null;
                                }
                                $variantImg = $variantImg ?: $v->product->firstImageUrl() ?: asset('images/logo.svg');
                            @endphp
                            <img src="{{ $variantImg }}" loading="lazy" class="w-12 h-14 sm:w-14 sm:h-16 object-cover rounded-lg border dark:border-gray-700 flex-shrink-0">

                            <div class="flex-1 min-w-0 text-start">
                                <h3 class="font-bold text-gray-900 dark:text-white break-words text-sm sm:text-base leading-snug">{{ $item->product_name }}</h3>
                                <p class="text-xs text-gray-500 mt-1 break-words">
                                    @if($item->color) {{ __('global.color_label') }} {{ $item->color }} @endif
                                    @if($item->color && $item->size) | @endif
                                    @if($item->size) {{ __('global.size_label') }} {{ $item->size }} @endif
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('global.qty_label_alt') }} {{ $item->quantity }} × {{ (int) round($item->unit_price) }} {{ __('global.currency') }}</p>
                            </div>

                            <span class="font-bold text-gray-900 dark:text-white flex-shrink-0 text-sm sm:text-base whitespace-nowrap">{{ (int) round($item->total) }} {{ __('global.currency') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Shipping Address Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm text-start">
                    <h2 class="text-lg font-bold mb-3 text-gray-900 dark:text-white">{{ __('global.shipping_address_title') }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $order->shipping_address }}</p>
                    @if($order->notes)
                        <div class="mt-4 pt-4 border-t dark:border-gray-700">
                            <span class="block text-xs font-semibold text-gray-500 mb-1">{{ __('global.customer_notes') }}</span>
                            <p class="text-sm text-gray-600 dark:text-gray-300 italic">" {{ $order->notes }} "</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Summary Widget (Right 1 col) -->
            <div class="md:col-span-1 space-y-6">
                <!-- Status & Costs -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm text-start">
                    <h2 class="text-lg font-bold mb-4 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.account_summary') }}</h2>

                    <div class="space-y-3 mb-6 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between">
                            <span>{{ __('global.order_status_label') }}</span>
                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                                @php
                                    $k = 'orders.status_' . $order->status;
                                    $t = __($k);
                                    echo $t === $k ? ucfirst(str_replace('_', ' ', $order->status)) : $t;
                                @endphp
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('global.payment_method_label') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                @if($order->payment_method === 'cash') {{ __('global.cash_on_delivery_status') }}
                                @elseif($order->payment_method === 'card') {{ __('global.credit_card_status') }}
                                @else {{ __('global.wallet_status') }} @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('global.payment_status_label') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                @if($order->payment_status === 'paid') {{ __('global.paid_status') }}
                                @elseif($order->payment_status === 'unpaid') {{ __('global.unpaid_status') }}
                                @else {{ __('global.failed_status') }} @endif
                            </span>
                        </div>

                        @if($order->status === 'delivered' && $order->delivered_at)
                        <div class="flex justify-between pt-1">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('return.delivered_date') }}</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400" dir="ltr">{{ $order->delivered_at->format('Y-m-d') }}</span>
                        </div>
                        <div class="mt-3">
                            @if($order->isWithinReturnWindow())
                                @php
                                    $daysElapsed = (int) $order->delivered_at->diffInDays(now());
                                    $pct = min(100, max(0, ($daysElapsed / 3) * 100));
                                    $daysLeft = 3 - $daysElapsed;
                                    $barColor = $daysLeft <= 1 ? 'bg-amber-500' : 'bg-emerald-500';
                                @endphp
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-1">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ 100 - $pct }}%"></div>
                                </div>
                                <p class="text-xs {{ $daysLeft <= 1 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} font-medium">
                                    {{ __('return.return_window_remaining', ['days' => $daysLeft]) }}
                                </p>
                            @else
                                <span class="inline-block text-xs text-gray-400 dark:text-gray-500 font-medium border border-gray-300 dark:border-gray-600 rounded-full px-3 py-0.5">
                                    {{ __('return.return_window_expired') }}
                                </span>
                            @endif
                        </div>
                        @endif

                        <div class="flex justify-between">
                            <span>{{ __('global.products_total_label') }}</span>
                            <span>{{ (int) round($order->subtotal) }} {{ __('global.currency') }}</span>
                        </div>
                        @if($order->discount > 0)
                        <div class="flex justify-between text-red-500">
                            <span>{{ __('global.discount_label') }}</span>
                            <span>-{{ (int) round($order->discount) }} {{ __('global.currency') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span>{{ __('global.shipping_cost_label_alt') }}</span>
                            <span>{{ $order->shipping_cost > 0 ? (int) round($order->shipping_cost) . ' ' . __('global.currency') : __('global.free_status') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-baseline pt-4 border-t dark:border-gray-700">
                        <span class="font-bold text-gray-900 dark:text-white">{{ __('global.final_total_label') }}</span>
                        <span class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ (int) round($order->total) }} {{ __('global.currency') }}</span>
                    </div>

                    @if(in_array($order->status, ['pending', 'confirmed']))
                    <div class="mt-6 pt-6 border-t dark:border-gray-700">
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm(@json(__('global.cancel_order_confirm')))">
                            @csrf
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5 text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ __('global.cancel_order_btn') }}</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($order->isWithinReturnWindow())
                        @php
                            $hasPendingReturn = $order->returnRequests()->whereIn('status', ['pending', 'approved'])->exists();
                            $hasPendingExchange = $order->exchanges()->whereIn('status', ['pending', 'approved'])->exists();
                        @endphp
                        @if(!$hasPendingReturn)
                        <div class="mt-6 pt-6 border-t dark:border-gray-700">
                            <form action="{{ route('returns.store', $order) }}" method="POST">
                                @csrf
                                <label class="block text-sm font-bold mb-2">{{ __('return.request_return') }}</label>
                                <textarea name="reason" rows="2" required placeholder="{{ __('return.reason_placeholder') }}" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-lg px-3 py-2 text-sm mb-3"></textarea>
                                <button type="submit" onclick="this.disabled=true;this.form.submit();" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition text-sm disabled:opacity-50 disabled:cursor-not-allowed">{{ __('return.submit_request') }}</button>
                            </form>
                        </div>
                        @endif
                        @if(!$hasPendingExchange)
                        <div class="mt-4">
                            <a href="{{ route('exchanges.create', $order) }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition text-sm text-center">
                                {{ __('return.request_exchange') }}
                            </a>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
