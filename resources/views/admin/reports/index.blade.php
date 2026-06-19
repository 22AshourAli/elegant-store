@extends('admin.layouts.app')

@section('title', __('global.admin_reports'))

@php use App\Helpers\Numbers; @endphp

<style>
@media print {
    @page { size: A4 landscape; margin: 12mm; }
    body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    nav, .sidebar, .notification-bell, .no-print, .btn-print, .btn-export,
    footer, [x-cloak], .quick-links-section { display: none !important; }
    .print-only { display: block !important; }
    .print-card { break-inside: avoid; box-shadow: none !important; border: 1px solid #e5e7eb !important; background: white !important; }
    .print-chart { max-width: 100% !important; height: auto !important; }
    .print-grid { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important; }
    .print-grid-3 { display: grid !important; grid-template-columns: 1fr 1fr 1fr !important; gap: 12px !important; }
    .kpi-card { border: 1px solid #e5e7eb; padding: 12px; border-radius: 8px; }
    table { font-size: 9pt; width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: right; }
    thead { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    canvas { max-width: 100% !important; max-height: 200px !important; }
    .print-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .print-header h1 { font-size: 16pt; font-weight: 800; color: #111; margin: 0; }
    .print-header .date { font-size: 9pt; color: #666; }
    .chart-box { page-break-inside: avoid; }
    .dark\:text-white, .text-white { color: #000000 !important; }
    .dark\:text-gray-100, .text-gray-100 { color: #111111 !important; }
    .dark\:text-gray-300, .text-gray-300 { color: #222222 !important; }
    .dark\:text-gray-400, .text-gray-400 { color: #333333 !important; }
}
.print-only { display: none; }
</style>

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Print Header --}}
    <div class="print-only print-header">
        <div>
            <h1>Elegant Store — {{ __('global.admin_reports') }}</h1>
            <p class="date">{{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    {{-- Header + Filters --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 no-print">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('global.admin_reports') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('global.admin_reports_stats') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap" x-data="{ period: '{{ $period }}' }">
            <select x-model="period" @change="window.location = '{{ route('admin.reports.index') }}?period=' + $event.target.value"
                class="text-sm font-bold border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>{{ __('global.analytics_today') }}</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>{{ __('global.analytics_this_week') }}</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>{{ __('global.analytics_this_month') }}</option>
                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>{{ __('global.analytics_quarter') }}</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>{{ __('global.analytics_this_year') }}</option>
                <option value="all" {{ $period === 'all' ? 'selected' : '' }}>{{ __('global.analytics_all_time') }}</option>
            </select>
            <a href="{{ route('admin.reports.export-csv', ['period' => $period]) }}"
               class="btn-export inline-flex items-center gap-1.5 text-xs font-extrabold px-3 py-2 rounded-xl border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ __('global.analytics_export_csv') }}
            </a>
            <button onclick="window.print()"
                class="btn-print inline-flex items-center gap-1.5 text-xs font-extrabold px-3 py-2 rounded-xl border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-700 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0014.586 3H7a2 2 0 00-2 2v2M7 17H5a2 2 0 01-2-2V9a2 2 0 012-2h2m4 10v-2a2 2 0 012-2h2a2 2 0 012 2v2m-8 0a2 2 0 002 2h4a2 2 0 002-2"/></svg>
                {{ __('global.analytics_print') }}
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="print-grid grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="kpi-card print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('global.analytics_total_sales') }}</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white" dir="ltr">{{ Numbers::formatCurrency($kpi['totalSales']) }}</p>
        </div>

        <div class="kpi-card print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('global.analytics_net_profit') }}</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold {{ $kpi['netProfit'] >= 0 ? 'text-slate-900 dark:text-white' : 'text-red-600 dark:text-red-400' }}" dir="ltr">{{ Numbers::formatCurrency($kpi['netProfit']) }}</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">{{ __('global.admin_profit_margin') }}: {{ Numbers::formatPercent($kpi['profitMargin']) }}</p>
        </div>

        <div class="kpi-card print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('global.analytics_completed_orders') }}</span>
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">{{ Numbers::formatInteger($kpi['completedOrders']) }}</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Online: {{ Numbers::formatInteger($kpi['onlineOrders']) }} / Offline: {{ Numbers::formatInteger($kpi['offlineOrders']) }}</p>
        </div>

        <div class="kpi-card print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('global.analytics_conversion_rate') }}</span>
                <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">{{ Numbers::formatPercent($kpi['conversionRate']) }}</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">{{ __('global.analytics_aov') }}: {{ Numbers::formatCurrency($kpi['aov']) }}</p>
        </div>
    </div>

    {{-- Revenue Breakdown --}}
    <div class="print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5 mb-6">
        <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-3">{{ __('global.admin_financial_report') }}</h3>
        <div class="print-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="bg-green-50 dark:bg-green-950/20 rounded-xl p-3 border border-green-100 dark:border-green-900/30">
                <span class="text-[10px] font-bold text-green-700 dark:text-green-400 uppercase tracking-wide">{{ __('global.admin_revenue') }}</span>
                <p class="text-lg font-extrabold text-green-700 dark:text-green-400 mt-1">{{ Numbers::formatInteger($kpi['totalProductRevenue']) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/20 rounded-xl p-3 border border-amber-100 dark:border-amber-900/30">
                <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wide">{{ __('global.admin_shipping') }}</span>
                <p class="text-lg font-extrabold text-amber-700 dark:text-amber-400 mt-1">{{ Numbers::formatInteger($kpi['totalShippingCollected']) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-950/20 rounded-xl p-3 border border-orange-100 dark:border-orange-900/30">
                <span class="text-[10px] font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wide">{{ __('global.admin_cost_of_goods') }}</span>
                <p class="text-lg font-extrabold text-orange-700 dark:text-orange-400 mt-1">{{ Numbers::formatInteger($kpi['totalCosts']) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
            </div>
            <div class="bg-red-50 dark:bg-red-950/20 rounded-xl p-3 border border-red-100 dark:border-red-900/30">
                <span class="text-[10px] font-bold text-red-700 dark:text-red-400 uppercase tracking-wide">{{ __('global.admin_expenses') }}</span>
                <p class="text-lg font-extrabold text-red-700 dark:text-red-400 mt-1">{{ Numbers::formatInteger($kpi['totalManualExpenses']) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
            </div>
            <div class="bg-indigo-50 dark:bg-indigo-950/20 rounded-xl p-3 border border-indigo-100 dark:border-indigo-900/30">
                <span class="text-[10px] font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wide">{{ __('global.analytics_net_profit') }}</span>
                <p class="text-lg font-extrabold {{ $kpi['netProfit'] >= 0 ? 'text-indigo-700 dark:text-indigo-400' : 'text-red-600' }} mt-1">{{ Numbers::formatInteger($kpi['netProfit']) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="print-grid grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
        <div class="print-card chart-box bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-4">{{ __('global.analytics_sales_trend') }}</h3>
            <div class="print-chart relative" style="height:260px">
                <canvas id="salesTrendChart"></canvas>
            </div>
            @if($chartValues->isEmpty())
            <p class="text-xs text-slate-400 dark:text-slate-500 text-center mt-4">{{ __('global.analytics_no_data') }}</p>
            @endif
        </div>

        <div class="print-card chart-box bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-4">{{ __('global.analytics_top_products') }}</h3>
            <div class="print-chart relative" style="height:260px">
                <canvas id="topProductsChart"></canvas>
            </div>
            @if($topProducts->isEmpty())
            <p class="text-xs text-slate-400 dark:text-slate-500 text-center mt-4">{{ __('global.analytics_no_data') }}</p>
            @endif
        </div>
    </div>

    {{-- Bottom Row: Colors, Sizes, Abandoned Carts --}}
    <div class="print-grid-3 grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
        <div class="print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-3">{{ __('global.analytics_top_colors') }}</h3>
            @if($topColors->isNotEmpty())
            <div class="space-y-2">
                @foreach($topColors as $color)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full border border-slate-200 dark:border-gray-600" style="background: {{ $color->color }}"></span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $color->color }}</span>
                    </div>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ Numbers::formatInteger($color->quantity) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 dark:text-slate-500">{{ __('global.analytics_no_data') }}</p>
            @endif
        </div>

        <div class="print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-3">{{ __('global.analytics_top_sizes') }}</h3>
            @if($topSizes->isNotEmpty())
            <div class="space-y-2">
                @foreach($topSizes as $size)
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $size->size }}</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ Numbers::formatInteger($size->quantity) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 dark:text-slate-500">{{ __('global.analytics_no_data') }}</p>
            @endif
        </div>

        <div class="print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-3">{{ __('global.analytics_abandoned_carts') }}</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('global.analytics_abandoned_total') }}</span>
                    <span class="text-sm font-extrabold text-slate-900 dark:text-white">{{ Numbers::formatInteger($abandoned['total']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('global.analytics_abandoned_value') }}</span>
                    <span class="text-sm font-extrabold text-slate-900 dark:text-white" dir="ltr">{{ Numbers::formatCurrency($abandoned['total_value']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('global.analytics_recovered') }}</span>
                    <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">{{ Numbers::formatInteger($abandoned['recovered']) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-gray-700">
                <a href="{{ route('admin.reports.cart-funnel') }}" class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('global.admin_report_cart_funnel') }} →</a>
            </div>
        </div>
    </div>

    {{-- Top Products Table --}}
    @if($topProducts->isNotEmpty())
    <div class="print-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-gray-700">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200">{{ __('global.analytics_top_products_table') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-gray-900/50 border-b border-slate-100 dark:border-gray-700">
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.admin_by_product') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.analytics_quantity') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.analytics_revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $product)
                    <tr class="border-b border-slate-100 dark:border-gray-700/50">
                        <td class="p-3 text-right text-xs font-bold text-slate-700 dark:text-slate-300">{{ $product->product_name }}</td>
                        <td class="p-3 text-right text-xs text-slate-500 dark:text-slate-400">{{ Numbers::formatInteger($product->quantity) }}</td>
                        <td class="p-3 text-right text-xs font-bold text-slate-700 dark:text-slate-300" dir="ltr">{{ Numbers::formatCurrency($product->total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Quick Links --}}
    <div class="quick-links-section no-print">
        <h3 class="text-sm font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">{{ __('global.analytics_more_reports') }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <a href="{{ route('admin.reports.returns') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200 dark:border-gray-700 p-3 hover:shadow-md hover:-translate-y-0.5 transition-all group text-center">
                <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ __('global.admin_report_returns_analysis') }}</span>
            </a>
            <a href="{{ route('admin.reports.aov-clv') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200 dark:border-gray-700 p-3 hover:shadow-md hover:-translate-y-0.5 transition-all group text-center">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ __('global.admin_report_aov_clv') }}</span>
            </a>
            <a href="{{ route('admin.reports.dead-stock') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200 dark:border-gray-700 p-3 hover:shadow-md hover:-translate-y-0.5 transition-all group text-center">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ __('global.admin_report_dead_stock') }}</span>
            </a>
            <a href="{{ route('admin.reports.payment-reconciliation') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200 dark:border-gray-700 p-3 hover:shadow-md hover:-translate-y-0.5 transition-all group text-center">
                <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ __('global.admin_report_payment_reconciliation') }}</span>
            </a>
            <a href="{{ route('admin.reports.cart-funnel') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200 dark:border-gray-700 p-3 hover:shadow-md hover:-translate-y-0.5 transition-all group text-center">
                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">{{ __('global.admin_report_cart_funnel') }}</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function() {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const fontFamily = "'Inter', system-ui, sans-serif";
    const currency = @json(__('global.currency'));

    const salesCtx = document.getElementById('salesTrendChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: '{{ __("global.analytics_sales") }}',
                    data: @json($chartValues),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y.toFixed(2) + ' ' + currency
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: textColor, font: { size: 10, family: fontFamily }, maxRotation: 0 },
                        grid: { color: gridColor }
                    },
                    y: {
                        ticks: { color: textColor, font: { size: 10, family: fontFamily }, callback: v => v.toLocaleString() },
                        grid: { color: gridColor }
                    }
                }
            }
        });
    }

    const productsCtx = document.getElementById('topProductsChart');
    if (productsCtx) {
        const labels = @json($topProducts->pluck('product_name'));
        const data = @json($topProducts->pluck('total'));
        const colors = ['#6366f1','#8b5cf6','#a855f7','#d946ef','#ec4899','#f43f5e','#f97316','#eab308','#22c55e','#14b8a6'];
        new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ __("global.analytics_revenue") }}',
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.x.toFixed(2) + ' ' + currency
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: textColor, font: { size: 10, family: fontFamily }, callback: v => v.toLocaleString() },
                        grid: { color: gridColor }
                    },
                    y: {
                        ticks: { color: textColor, font: { size: 10, family: fontFamily } },
                        grid: { display: false }
                    }
                }
            }
        });
    }
})();
</script>
@endpush