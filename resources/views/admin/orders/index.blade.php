@extends('admin.layouts.app')

@section('page-title', __('global.admin_manage_orders'))

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('global.admin_manage_orders') }}</h1>

    <!-- Filter form -->
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="order_type" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
            <option value="">{{ __('global.admin_all_types') }}</option>
            <option value="online" {{ request('order_type') == 'online' ? 'selected' : '' }}>{{ __('global.admin_online') }}</option>
            <option value="offline" {{ request('order_type') == 'offline' ? 'selected' : '' }}>{{ __('global.admin_offline') }}</option>
        </select>

        <select name="status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
            <option value="">{{ __('global.admin_all_statuses') }}</option>
            @foreach(['pending','confirmed','processing','shipped','delivered','cancelled','returned'] as $st)
                @php
                    $k = 'orders.status_' . $st;
                    $t = __($k);
                    $label = $t === $k ? ucfirst(str_replace('_', ' ', $st)) : $t;
                @endphp
                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <select name="branch_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
            <option value="">{{ __('global.admin_all_branches') }}</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
            {{ __('global.admin_filter') }}
        </button>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    @if($orders->isEmpty())
        <div class="p-12 text-center text-gray-500">{{ __('global.admin_no_orders') }}</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600 text-sm font-semibold text-gray-500 dark:text-gray-300">
                        <th class="p-4">{{ __('global.admin_order_no') }}</th>
                        <th class="p-4">{{ __('global.admin_customer') }}</th>
                        <th class="p-4 hidden md:table-cell">{{ __('global.admin_branch') }}</th>
                        <th class="p-4 hidden md:table-cell">{{ __('global.admin_payment_method') }}</th>
                        <th class="p-4">{{ __('global.admin_status') }}</th>
                        <th class="p-4">{{ __('global.admin_total') }}</th>
                        <th class="p-4 hidden md:table-cell">{{ __('global.admin_date') }}</th>
                        <th class="p-4 text-left">{{ __('global.admin_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($orders as $order)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 text-sm text-gray-900 dark:text-gray-200 cursor-pointer transition-colors even:bg-gray-50/50 dark:even:bg-gray-700/20"
                        onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold">#{{ $order->id }}</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $order->order_type === 'offline' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                    {{ $order->order_type === 'offline' ? __('global.admin_offline') : __('global.admin_online') }}
                                </span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-1.5">
                                <span>{{ $order->user->name }}</span>
                                @if(!empty($order->user->email))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">أونلاين</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">أوفلاين</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-4 hidden md:table-cell">{{ $order->branch->name ?? __('global.admin_not_specified') }}</td>
                        <td class="p-4 hidden md:table-cell">
                            @if($order->payment_method === 'cash')
                                {{ __('global.admin_cash') }}
                            @elseif($order->payment_method === 'card')
                                {{ __('global.admin_card') }}
                            @else
                                {{ __('global.admin_wallet') }}
                            @endif
                        </td>
                        <td class="p-4">
                             <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($order->status === 'delivered') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                @elseif($order->status === 'pending') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                @elseif($order->status === 'processing') bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300
                                @elseif($order->status === 'confirmed') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300
                                @elseif($order->status === 'shipped') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                @else bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300 @endif">
                                @php
                                    $k = 'orders.status_' . $order->status;
                                    $t = __($k);
                                    echo $t === $k ? ucfirst(str_replace('_', ' ', $order->status)) : $t;
                                @endphp
                            </span>
                        </td>
                        <td class="p-4 font-bold">{{ (int) round($order->total) }} {{ __('global.currency') }}</td>
                        <td class="p-4 hidden md:table-cell">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-4 text-right md:text-left">
                            <a href="{{ route('admin.orders.show', $order) }}" onclick="event.stopPropagation()" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold">
                                {{ __('global.admin_view_update') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
        $nextCursor = $result['next_cursor'] ?? null;
        $prevCursor = $result['prev_cursor'] ?? null;
        $hasMore = $result['has_more'] ?? false;
        @endphp
        <x-admin-cursor-pagination :next-cursor="$nextCursor" :prev-cursor="$prevCursor" :has-more="$hasMore" />
    @endif
</div>
@endsection
