@extends('layouts.store')

@section('content')
<div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-extrabold mb-8 text-gray-900 dark:text-white">{{ __('global.my_orders') }}</h1>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-lg font-bold mb-1">{{ __('global.no_orders_yet') }}</h3>
                <p class="text-gray-500 mb-6">{{ __('global.empty_orders_desc') }}</p>
                <a href="{{ route('home') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors inline-block">{{ __('global.shop_now') }}</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-sm font-semibold border-b dark:border-gray-600">
                            <th class="p-4">{{ __('global.order_no') }}</th>
                            <th class="p-4">{{ __('global.order_date') }}</th>
                            <th class="p-4">{{ __('global.payment_method') }}</th>
                            <th class="p-4">{{ __('global.payment_status_label') }}</th>
                            <th class="p-4">{{ __('global.order_status') }}</th>
                            <th class="p-4">{{ __('global.total') }}</th>
                            <th class="p-4 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('global.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        @foreach($orders as $order)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 text-sm text-gray-900 dark:text-gray-200">
                            <td class="p-4 font-bold">#{{ $order->id }}</td>
                            <td class="p-4">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="p-4">
                                @if($order->payment_method === 'cash')
                                    {{ __('global.cash_on_delivery_status') }}
                                @elseif($order->payment_method === 'card')
                                    {{ __('global.credit_card_status') }}
                                @else
                                    {{ __('global.wallet_status') }}
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($order->payment_status === 'paid') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                    @elseif($order->payment_status === 'unpaid') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @endif">
                                    @if($order->payment_status === 'paid') {{ __('global.paid_status') }}
                                    @elseif($order->payment_status === 'unpaid') {{ __('global.unpaid_status') }}
                                    @else {{ __('global.failed_status') }} @endif
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($order->status === 'delivered') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                    @elseif($order->status === 'pending') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 @endif">
                                    @php
                                        $k = 'orders.status_' . $order->status;
                                        $t = __($k);
                                        echo $t === $k ? ucfirst(str_replace('_', ' ', $order->status)) : $t;
                                    @endphp
                                </span>
                            </td>
                            <td class="p-4 font-bold">{{ (int) round($order->total) }} {{ __('global.currency') }}</td>
                            <td class="p-4 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold">{{ __('global.order_details') }} {!! app()->getLocale() === 'ar' ? '&larr;' : '&rarr;' !!}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="p-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
