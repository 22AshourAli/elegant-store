@extends('admin.layouts.app')

@section('title', __('global.admin_dashboard'))
@section('page-title', __('global.admin_dashboard'))

@php use App\Helpers\Numbers; @endphp

<style>
@media print {
    @page { size: A4 landscape; margin: 12mm; }
    body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    nav, .sidebar, .notification-bell, .no-print, .auto-refresh-btn,
    footer, [x-cloak], .export-dropdown, .filter-bar { display: none !important; }
    .print-block { display: block !important; }
    .print-card { break-inside: avoid; box-shadow: none !important; border: 1px solid #e5e7eb !important; }
    .print-grid-4 { display: grid !important; grid-template-columns: 1fr 1fr 1fr 1fr !important; gap: 12px !important; }
    .print-grid-2 { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important; }
    canvas { max-width: 100% !important; max-height: 220px !important; }
    table { font-size: 9pt !important; width: 100% !important; border-collapse: collapse !important; }
    th, td { border: 1px solid #d1d5db !important; padding: 4px 6px !important; text-align: right !important; }
    thead { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .kpi-card { padding: 8px !important; }
    .kpi-card h3 { font-size: 14pt !important; }
    .kpi-card span.label { font-size: 7pt !important; }
    .chart-container { page-break-inside: avoid; max-height: 220px !important; }
    .dark\:text-white, .text-white { color: #000000 !important; }
    .dark\:text-gray-100, .text-gray-100 { color: #111111 !important; }
    .dark\:text-gray-300, .text-gray-300 { color: #222222 !important; }
    .dark\:text-gray-400, .text-gray-400 { color: #333333 !important; }
}
</style>

@section('content')
<div class="space-y-6 text-start" x-data="dashboardPage()">
    {{-- Filter Bar --}}
    <div class="filter-bar bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl border border-gray-200/60 dark:border-amber-500/20 shadow-lg shadow-amber-500/5 p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('global.admin_period') }}:</span>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm font-bold rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 transition cursor-pointer min-w-[130px]">
                        <span x-text="{
                            'today': '{{ __('global.period_today') }}',
                            '7days': '{{ __('global.period_7days') }}',
                            'month': '{{ __('global.period_month') }}',
                            'quarter': '{{ __('global.period_quarter') }}',
                            'year': '{{ __('global.period_year') }}',
                            'all': '{{ __('global.period_all') }}'
                        }['{{ $period }}'] || '{{ __('global.period_month') }}'"></span>
                        <svg class="w-3.5 h-3.5 text-amber-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak
                        class="absolute top-full right-0 mt-1 min-w-[160px] bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-xl shadow-lg border border-amber-500/20 z-50 py-1 overflow-hidden">
                        @php $periods = ['today' => 'period_today', '7days' => 'period_7days', 'month' => 'period_month', 'quarter' => 'period_quarter', 'year' => 'period_year', 'all' => 'period_all']; @endphp
                        @foreach($periods as $key => $label)
                        <a href="{{ url()->current() }}?period={{ $key }}"
                           class="flex items-center gap-2 px-3 py-2 text-xs font-bold transition {{ $period === $key ? 'text-amber-600 dark:text-amber-400 bg-amber-500/10' : 'text-gray-600 dark:text-gray-400 hover:bg-amber-500/5 hover:text-amber-600 dark:hover:text-amber-400' }}">
                            @if($period === $key)
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                            <span class="w-3.5 h-3.5"></span>
                            @endif
                            {{ __("global.$label") }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 ms-auto no-print">
                @if(request('period'))
                <a href="{{ url()->current() }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ __('global.period_all') }}
                </a>
                @endif
                <button @click="toggleAutoRefresh()"
                    :class="autoRefresh ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600'"
                    class="auto-refresh-btn flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-xl transition-all duration-200">
                    <svg class="w-3.5 h-3.5" :class="autoRefresh ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span x-text="autoRefresh ? countdown + 's' : '{{ __('global.auto_refresh') }}'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="print-grid-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        {{-- Net Profit --}}
        <div class="kpi-card print-card bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-between lg:col-span-2">
            <div class="space-y-2">
                <span class="label text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_net_profit') }}</span>
                <h3 class="text-2xl font-extrabold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ Numbers::formatInteger($netProfit) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span>
                </h3>
                <div class="flex items-center gap-4 text-xs text-gray-400 mt-1 flex-wrap">
                    <span>{{ __('global.admin_revenue') }}: <strong class="text-gray-600 dark:text-gray-300">{{ Numbers::formatInteger($totalProductRevenue) }}</strong></span>
                    <span>{{ __('global.admin_shipping') }}: <strong class="text-amber-600">{{ Numbers::formatInteger($totalShippingCollected) }}</strong></span>
                    <span>{{ __('global.admin_cost') }}: <strong class="text-orange-600">{{ Numbers::formatInteger($totalCosts) }}</strong></span>
                    <span>{{ __('global.admin_expenses') }}: <strong class="text-red-600">{{ Numbers::formatInteger($totalManualExpenses) }}</strong></span>
                </div>
                <div class="flex items-center gap-3 text-xs mt-1">
                    <span>{{ __('global.admin_profit_margin') }}: <strong class="{{ $profitMargin >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ Numbers::formatPercent($profitMargin) }}</strong></span>
                    <span>{{ __('global.admin_aov') }}: <strong class="text-indigo-600 dark:text-indigo-400">{{ Numbers::formatCurrency($aov) }}</strong></span>
                </div>
            </div>
            <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        {{-- Orders --}}
        <div class="kpi-card print-card bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-between">
            <div class="space-y-2">
                <span class="label text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_orders_count') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ Numbers::formatInteger($totalOrders) }}</h3>
                <div class="flex gap-3 text-xs mt-1">
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">Online: {{ Numbers::formatInteger($onlineOrders) }}</span>
                    <span class="text-amber-600 dark:text-amber-400 font-bold">Offline: {{ Numbers::formatInteger($offlineOrders) }}</span>
                </div>
            </div>
            <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
        </div>

        {{-- Customers --}}
        <div class="kpi-card print-card bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-between">
            <div class="space-y-2">
                <span class="label text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_customers_count') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ Numbers::formatInteger($totalCustomers) }}</h3>
                <div class="text-xs text-gray-400 mt-1">{{ __('global.admin_total_customers') }}</div>
            </div>
            <div class="p-3.5 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        {{-- Returns --}}
        <a href="{{ route('admin.returns.index') }}" class="kpi-card print-card block bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between hover:shadow-md hover:border-amber-300 hover:-translate-y-0.5 transition-all duration-300">
            <div class="space-y-2">
                <span class="label text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_return_requests') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ Numbers::formatInteger($returnRequestCount) }}</h3>
                <div class="text-xs text-gray-400">{{ __('global.admin_pending') }} <span class="text-amber-600 font-bold">{{ Numbers::formatInteger($returnRequestPending) }}</span></div>
            </div>
            <div class="p-3.5 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            </div>
        </a>

        {{-- Exchanges --}}
        <a href="{{ route('admin.exchanges.index') }}" class="kpi-card print-card block bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between hover:shadow-md hover:border-indigo-300 hover:-translate-y-0.5 transition-all duration-300">
            <div class="space-y-2">
                <span class="label text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_exchange_requests') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ Numbers::formatInteger($exchangeCount) }}</h3>
                <div class="text-xs text-gray-400">{{ __('global.admin_pending') }} <span class="text-indigo-600 font-bold">{{ Numbers::formatInteger($exchangePending) }}</span></div>
            </div>
            <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
        </a>

        {{-- Low Stock --}}
        <div class="kpi-card print-card bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-between">
            <div class="space-y-2">
                <span class="label text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_low_stock') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ Numbers::formatInteger($lowStockCount) }}</h3>
                <div class="text-xs text-gray-400 mt-1">{{ __('global.admin_less_than') }} {{ config('store.low_stock_threshold', 5) }} {{ __('global.admin_piece') }}</div>
            </div>
            <div class="p-3.5 {{ $lowStockCount > 0 ? 'bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400' }} rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>
    </div>

    {{-- Financial Report Card --}}
    <div class="print-card bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="print-block flex flex-wrap items-center justify-between gap-3 mb-6">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>{{ __('global.admin_financial_report') }}</span>
            </h4>
            <div class="no-print flex items-center gap-2">
                <span class="text-xs text-gray-400">{{ __('global.admin_total') }}</span>
                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/20 px-2 py-0.5 rounded">{{ Numbers::formatInteger($totalProductRevenue + $totalShippingCollected) }} {{ __('global.currency') }}</span>
                <div class="export-dropdown relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                            class="px-2.5 py-1.5 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>{{ __('global.admin_export') }}</span>
                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" style="display: none"
                         class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-700 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600 z-50 py-1">
                        <a href="{{ route('admin.dashboard.export-csv', request()->query()) }}"
                           class="block px-3 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">CSV</a>
                        <a href="{{ route('admin.dashboard.export-excel', request()->query()) }}"
                           class="block px-3 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400 hover:bg-gray-100 dark:hover:bg-gray-600">Excel</a>
                        <a href="{{ route('admin.dashboard.export-pdf', request()->query()) }}"
                           class="block px-3 py-2 text-xs font-semibold text-red-700 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-600">PDF</a>
                    </div>
                </div>
                <button onclick="window.print()" class="px-2.5 py-1.5 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </button>
            </div>
        </div>

        <div class="print-grid-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-green-50 dark:bg-green-950/20 rounded-xl p-4 border border-green-100 dark:border-green-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-wide">{{ __('global.admin_revenue') }}</span>
                </div>
                <p class="text-2xl font-extrabold text-green-700 dark:text-green-400">{{ Numbers::formatInteger($totalProductRevenue) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-green-600/70 dark:text-green-500/70 mt-1">{{ __('global.admin_product_sales') }}</p>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/20 rounded-xl p-4 border border-amber-100 dark:border-amber-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wide">{{ __('global.admin_shipping_expenses') }}</span>
                </div>
                <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-400">{{ Numbers::formatInteger($totalShippingCollected) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-amber-600/70 dark:text-amber-500/70 mt-1">{{ __('global.admin_collected_from_customers') }}</p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-950/20 rounded-xl p-4 border border-orange-100 dark:border-orange-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wide">{{ __('global.admin_cost_of_goods') }}</span>
                </div>
                <p class="text-2xl font-extrabold text-orange-700 dark:text-orange-400">{{ Numbers::formatInteger($totalCosts) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-orange-600/70 dark:text-orange-500/70 mt-1">{{ __('global.admin_cost_from_suppliers') }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-950/20 rounded-xl p-4 border border-red-100 dark:border-red-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-wide">{{ __('global.admin_other_expenses') }}</span>
                </div>
                <p class="text-2xl font-extrabold text-red-700 dark:text-red-400">{{ Numbers::formatInteger($totalManualExpenses) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-red-600/70 dark:text-red-500/70 mt-1">{{ __('global.admin_rent_salaries_bills') }}</p>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>{{ __('global.admin_net_profit_eq') }} {{ Numbers::formatInteger($totalProductRevenue) }}
                {{ __('global.admin_minus_cost') }} {{ Numbers::formatInteger($totalCosts) }}
                {{ __('global.admin_minus_shipping') }} {{ Numbers::formatInteger($totalShippingCollected) }}
                {{ __('global.admin_minus_expenses') }} {{ Numbers::formatInteger($totalManualExpenses) }}</span>
            </div>
            <div class="text-lg font-extrabold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                = {{ Numbers::formatInteger($netProfit) }} {{ __('global.currency') }}
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="print-grid-2 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="print-card chart-container bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('global.admin_revenue_and_orders') }}</h4>
                <div class="flex gap-2 text-xs">
                    <span class="flex items-center gap-1"><span class="w-3 h-0.5 bg-indigo-500 inline-block"></span> {{ __('global.admin_revenue') }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-0.5 bg-emerald-500 inline-block dashed"></span> {{ __('global.admin_orders') }}</span>
                </div>
            </div>
            <div class="h-72 w-full relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="print-card chart-container bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('global.admin_order_distribution') }}</h4>
            </div>
            <div class="h-72 w-full relative flex items-center justify-center">
                <canvas id="ordersPieChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Low Stock Table --}}
    <div class="print-card bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ __('global.admin_low_stock') }}</span>
            </h4>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('global.admin_minimum_threshold') }} {{ config('store.low_stock_threshold', 5) }} {{ __('global.admin_piece') }}</span>
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
                            {{ Numbers::formatInteger($item->stock) }} {{ __('global.admin_piece') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($item->stock <= 1)
                                <span class="bg-red-100 text-red-800 text-xs px-2.5 py-0.5 rounded-full dark:bg-red-900/30 dark:text-red-300 font-semibold">{{ __('global.admin_critical') }}</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2.5 py-0.5 rounded-full dark:bg-yellow-900/30 dark:text-yellow-300 font-semibold">{{ __('global.admin_low') }}</span>
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

    {{-- Recent Expenses --}}
    @if($recentExpenses && $recentExpenses->count() > 0)
    <div class="print-card bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('global.admin_recent_expenses') }}</h4>
            <a href="{{ route('admin.expenses.index') }}" class="text-xs text-indigo-600 hover:underline font-semibold">{{ __('global.admin_view_all') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400">
                    <tr><th class="px-6 py-3">{{ __('global.admin_category') }}</th><th class="px-6 py-3">{{ __('global.admin_description') }}</th><th class="px-6 py-3">{{ __('global.admin_amount') }}</th><th class="px-6 py-3">{{ __('global.admin_date') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($recentExpenses as $expense)
                    <tr>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700">{{ $expense->category }}</span></td>
                        <td class="px-6 py-4 max-w-xs truncate">{{ $expense->description }}</td>
                        <td class="px-6 py-4 font-bold text-red-600">{{ Numbers::formatCurrency($expense->amount) }}</td>
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
    return {
        autoRefresh: false,
        countdown: 30,
        _interval: null,

        init() {
            const saved = localStorage.getItem('dashboard_auto_refresh');
            if (saved === 'true') {
                this.autoRefresh = true;
                this._startInterval();
            }
        },

        destroy() {
            this._clearInterval();
        },

        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            localStorage.setItem('dashboard_auto_refresh', this.autoRefresh);
            if (this.autoRefresh) {
                this.countdown = 30;
                this._startInterval();
            } else {
                this._clearInterval();
            }
        },

        _startInterval() {
            this.countdown = 30;
            this._clearInterval();
            this._interval = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    window.location.reload();
                }
            }, 1000);
        },

        _clearInterval() {
            if (this._interval) {
                clearInterval(this._interval);
                this._interval = null;
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', function () {
    const raw = @json(array_values($chartData));
    const labels = @json($chartLabels);
    const revenue = raw.map(r => r.revenue || 0);
    const counts = raw.map(r => r.count || 0);

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)';

    const ctxRev = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRev, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: '{{ __("global.admin_revenue") }} ({{ __("global.currency") }})',
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
                    label: '{{ __("global.admin_orders") }}',
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
                    labels: { font: { family: 'Cairo', size: 11 }, boxWidth: 14, padding: 12, color: textColor }
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
                    ticks: { font: { family: 'Cairo', size: 10 }, color: textColor }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: '{{ __("global.currency") }}', font: { family: 'Cairo' }, color: textColor },
                    grid: { color: gridColor },
                    ticks: { font: { family: 'Cairo', size: 10 }, color: textColor }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: '{{ __("global.admin_order_count") }}', font: { family: 'Cairo' }, color: textColor },
                    grid: { drawOnChartArea: false },
                    ticks: { stepSize: 1, font: { family: 'Cairo', size: 10 }, color: textColor }
                }
            }
        }
    });

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
                    labels: { font: { family: 'Cairo', size: 12 }, padding: 16, color: textColor }
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