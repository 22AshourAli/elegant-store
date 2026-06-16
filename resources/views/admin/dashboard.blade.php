@extends('admin.layouts.app')

@section('title', __('global.admin_dashboard'))
@section('page-title', __('global.admin_dashboard'))

@section('content')
<div class="space-y-6 text-start" x-data="dashboardPage()">
    <!-- Filter Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">الفترة:</span>
            @php $periods = ['today' => 'اليوم', '7days' => 'آخر 7 أيام', 'month' => 'هذا الشهر', 'quarter' => 'آخر 3 شهور', 'year' => 'هذا العام', 'all' => 'الكل']; @endphp
            @foreach($periods as $key => $label)
            <a href="{{ url()->current() }}?period={{ $key }}"
               class="px-3 py-1.5 text-sm font-semibold rounded-xl transition-all
               {{ $period === $key ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">

        <!-- Net Profit -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between lg:col-span-2">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">صافي الربح</span>
                <h3 class="text-2xl font-extrabold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format((int) round($netProfit)) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span>
                </h3>
                <div class="flex items-center gap-4 text-xs text-gray-400 mt-1 flex-wrap">
                    <span>إيراد: <strong class="text-gray-600 dark:text-gray-300">{{ number_format((int) round($totalProductRevenue)) }}</strong></span>
                    <span>شحن: <strong class="text-amber-600">{{ number_format((int) round($totalShippingCollected)) }}</strong></span>
                    <span>تكلفة: <strong class="text-orange-600">{{ number_format((int) round($totalCosts)) }}</strong></span>
                    <span>مصروفات: <strong class="text-red-600">{{ number_format((int) round($totalManualExpenses)) }}</strong></span>
                </div>
                <div class="flex items-center gap-3 text-xs mt-1">
                    <span>هامش الربح: <strong class="{{ $profitMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $profitMargin }}%</strong></span>
                    <span>متوسط الطلب: <strong class="text-indigo-600">{{ number_format((int) round($aov)) }} {{ __('global.currency') }}</strong></span>
                </div>
            </div>
            <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        <!-- Orders -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_orders_count') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $totalOrders }}</h3>
                <div class="flex gap-3 text-xs mt-1">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">Online: {{ $onlineOrders }}</span>
                    <span class="text-amber-600 dark:text-amber-400 font-bold">Offline: {{ $offlineOrders }}</span>
                </div>
            </div>
            <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
        </div>

        <!-- Customers -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_customers_count') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $totalCustomers }}</h3>
                <div class="text-xs text-gray-400 mt-1">إجمالي العملاء المسجلين</div>
            </div>
            <div class="p-3.5 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- Returns -->
        <a href="{{ route('admin.returns.index') }}" class="block bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between hover:shadow-md hover:border-amber-300 transition">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">طلبات الإرجاع</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $returnRequestCount }}</h3>
                <div class="text-xs text-gray-400">قيد الانتظار: <span class="text-amber-600 font-bold">{{ $returnRequestPending }}</span></div>
            </div>
            <div class="p-3.5 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            </div>
        </a>

        <!-- Exchanges -->
        <a href="{{ route('admin.exchanges.index') }}" class="block bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between hover:shadow-md hover:border-indigo-300 transition">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">طلبات الاستبدال</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $exchangeCount }}</h3>
                <div class="text-xs text-gray-400">قيد الانتظار: <span class="text-indigo-600 font-bold">{{ $exchangePending }}</span></div>
            </div>
            <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
        </a>

        <!-- Low Stock -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_low_stock') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $lowStockCount }}</h3>
                <div class="text-xs text-gray-400 mt-1">أقل من {{ config('store.low_stock_threshold', 5) }} قطع</div>
            </div>
            <div class="p-3.5 {{ $lowStockCount > 0 ? 'bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400' }} rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Financial Report Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>التقرير المالي</span>
            </h4>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">الإجمالي</span>
                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/20 px-2 py-0.5 rounded">{{ number_format((int) round($totalProductRevenue + $totalShippingCollected)) }} {{ __('global.currency') }}</span>
                <div class="flex gap-1">
                    <a href="{{ route('admin.dashboard.export-csv', request()->query()) }}"
                       class="px-2.5 py-1.5 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        CSV
                    </a>
                    <a href="{{ route('admin.dashboard.export-excel', request()->query()) }}"
                       class="px-2.5 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 hover:bg-emerald-100 dark:hover:bg-emerald-950/40 rounded-xl transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </a>
                    <a href="{{ route('admin.dashboard.export-pdf', request()->query()) }}"
                       class="px-2.5 py-1.5 text-xs font-bold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-950/40 rounded-xl transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </a>
                    <button onclick="window.print()" class="px-2.5 py-1.5 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        طباعة
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-green-50 dark:bg-green-950/20 rounded-xl p-4 border border-green-100 dark:border-green-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-wide">إيراد</span>
                </div>
                <p class="text-2xl font-extrabold text-green-700 dark:text-green-400">{{ number_format((int) round($totalProductRevenue)) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-green-600/70 dark:text-green-500/70 mt-1">مبيعات المنتجات</p>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/20 rounded-xl p-4 border border-amber-100 dark:border-amber-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wide">مصاريف الشحن</span>
                </div>
                <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-400">{{ number_format((int) round($totalShippingCollected)) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-amber-600/70 dark:text-amber-500/70 mt-1">محصّل من العملاء</p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-950/20 rounded-xl p-4 border border-orange-100 dark:border-orange-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wide">تكلفة البضاعة</span>
                </div>
                <p class="text-2xl font-extrabold text-orange-700 dark:text-orange-400">{{ number_format((int) round($totalCosts)) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-orange-600/70 dark:text-orange-500/70 mt-1">تكلفة الشراء من الموردين</p>
            </div>
            <div class="bg-red-50 dark:bg-red-950/20 rounded-xl p-4 border border-red-100 dark:border-red-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-wide">مصروفات أخرى</span>
                </div>
                <p class="text-2xl font-extrabold text-red-700 dark:text-red-400">{{ number_format((int) round($totalManualExpenses)) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-red-600/70 dark:text-red-500/70 mt-1">إيجار، رواتب، فواتير</p>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>صافي الربح = إيراد {{ number_format((int) round($totalProductRevenue)) }}
                - تكلفة {{ number_format((int) round($totalCosts)) }}
                - شحن {{ number_format((int) round($totalShippingCollected)) }}
                - مصروفات {{ number_format((int) round($totalManualExpenses)) }}</span>
            </div>
            <div class="text-lg font-extrabold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                = {{ number_format((int) round($netProfit)) }} {{ __('global.currency') }}
            </div>
        </div>
    </div>

    <!-- Charts Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">الإيرادات والطلب</h4>
                <div class="flex gap-2 text-xs">
                    <span class="flex items-center gap-1"><span class="w-3 h-0.5 bg-indigo-500 inline-block"></span> الإيراد</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-0.5 bg-emerald-500 inline-block dashed"></span> الطلبات</span>
                </div>
            </div>
            <div class="h-72 w-full relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">توزيع الطلبات</h4>
            </div>
            <div class="h-72 w-full relative flex items-center justify-center">
                <canvas id="ordersPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ __('global.admin_low_stock') }}</span>
            </h4>
            <span class="text-xs text-gray-500 dark:text-gray-400">الحد الأدنى: أقل من {{ config('store.low_stock_threshold', 5) }} قطع</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">{{ __('global.product') }}</th>
                        <th class="px-6 py-3">{{ __('global.color') }}</th>
                        <th class="px-6 py-3">{{ __('global.size') }}</th>
                        <th class="px-6 py-3">{{ __('global.admin_branch') }}</th>
                        <th class="px-6 py-3">{{ __('global.admin_stock') }}</th>
                        <th class="px-6 py-3">{{ __('global.admin_status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($lowStockItems as $item)
                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                            {{ $item->product_name }}
                            @if($item->sku)
                            <span class="block text-xs font-normal text-gray-400">{{ $item->sku }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $item->color ?: '-' }}</td>
                        <td class="px-6 py-4">{{ $item->size ?: '-' }}</td>
                        <td class="px-6 py-4 text-indigo-600 dark:text-indigo-400">{{ $item->branch_name }}</td>
                        <td class="px-6 py-4 font-bold {{ $item->stock <= 1 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                            {{ $item->stock }} قطعة
                        </td>
                        <td class="px-6 py-4">
                            @if($item->stock <= 1)
                                <span class="bg-red-100 text-red-800 text-xs px-2.5 py-0.5 rounded-full dark:bg-red-900/30 dark:text-red-300 font-semibold">حرج جداً</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2.5 py-0.5 rounded-full dark:bg-yellow-900/30 dark:text-yellow-300 font-semibold">منخفض</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-400 dark:text-gray-500">
                            {{ __('global.admin_no_low_stock') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Expenses -->
    @if($recentExpenses && $recentExpenses->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white">آخر المصروفات</h4>
            <a href="{{ route('admin.expenses.index') }}" class="text-xs text-indigo-600 hover:underline font-semibold">عرض الكل</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400">
                    <tr><th class="px-6 py-3">التصنيف</th><th class="px-6 py-3">الوصف</th><th class="px-6 py-3">المبلغ</th><th class="px-6 py-3">التاريخ</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($recentExpenses as $expense)
                    <tr>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700">{{ $expense->category }}</span></td>
                        <td class="px-6 py-4 max-w-xs truncate">{{ $expense->description }}</td>
                        <td class="px-6 py-4 font-bold text-red-600">{{ number_format((int) round($expense->amount)) }} {{ __('global.currency') }}</td>
                        <td class="px-6 py-4 text-xs">{{ $expense->expense_date->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function dashboardPage() {
    return {};
}

document.addEventListener('DOMContentLoaded', function () {
    const raw = @json(array_values($chartData));
    const labels = @json($chartLabels);
    const revenue = raw.map(r => r.revenue || 0);
    const counts = raw.map(r => r.count || 0);

    // Revenue + Orders line chart
    const ctxRev = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRev, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'الإيراد ({{ __("global.currency") }})',
                    data: revenue,
                    borderColor: '#6366f1',
                    backgroundColor: function(ctx) {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                        g.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
                        g.addColorStop(1, 'rgba(99, 102, 241, 0)');
                        return g;
                    },
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    yAxisID: 'y'
                },
                {
                    label: 'الطلبات',
                    data: counts,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderWidth: 2.5,
                    borderDash: [6, 4],
                    tension: 0.1,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { family: 'Cairo', size: 11 }, boxWidth: 14, padding: 12 }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { family: 'Cairo' },
                    bodyFont: { family: 'Cairo' },
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Cairo', size: 10 } }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: '{{ __("global.currency") }}', font: { family: 'Cairo' } },
                    grid: { color: 'rgba(156, 163, 175, 0.1)' },
                    ticks: { font: { family: 'Cairo', size: 10 } }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'عدد الطلبات', font: { family: 'Cairo' } },
                    grid: { drawOnChartArea: false },
                    ticks: { stepSize: 1, font: { family: 'Cairo', size: 10 } }
                }
            }
        }
    });

    // Orders pie chart
    const ctxPie = document.getElementById('ordersPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Online', 'Offline'],
            datasets: [{
                data: [{{ $onlineOrders }}, {{ $offlineOrders }}],
                backgroundColor: ['#6366f1', '#f59e0b'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { family: 'Cairo', size: 12 }, padding: 16 }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { family: 'Cairo' },
                    bodyFont: { family: 'Cairo' },
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                            return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
