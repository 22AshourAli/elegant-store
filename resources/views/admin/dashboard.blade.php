@extends('admin.layouts.app')

@section('title', __('global.admin_dashboard'))
@section('page-title', __('global.admin_dashboard'))

@section('content')
<div class="space-y-8 text-start">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">

        <!-- Stat: Net Profit (this month) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between lg:col-span-2">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">صافي الربح (هذا الشهر)</span>
                <h3 class="text-2xl font-extrabold {{ $monthlyNetProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ (int) round($monthlyNetProfit) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span>
                </h3>
                <div class="flex items-center gap-4 text-xs text-gray-400 mt-1">
                    <span>إيراد: <strong class="text-gray-600 dark:text-gray-300">{{ (int) round($monthlyProductRevenue) }}</strong></span>
                    <span>شحن: <strong class="text-amber-600">{{ (int) round($monthlyShippingCollected) }}</strong></span>
                    <span>تكلفة: <strong class="text-orange-600">{{ (int) round($monthlyCosts) }}</strong></span>
                    <span>مصروفات: <strong class="text-red-600">{{ (int) round($monthlyExpenses) }}</strong></span>
                </div>
                <div class="flex items-center gap-3 text-xs mt-1">
                    <span>هامش الربح: <strong class="{{ $monthlyProfitMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $monthlyProfitMargin }}%</strong></span>
                    <span>متوسط الطلب: <strong class="text-indigo-600">{{ (int) round($monthlyAov) }} {{ __('global.currency') }}</strong></span>
                </div>
            </div>
            <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        <!-- Stat: Net Profit (all time) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">صافي الربح (الإجمالي)</span>
                <h3 class="text-2xl font-extrabold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ (int) round($netProfit) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span>
                </h3>
                <div class="text-xs mt-1">
                    <span>هامش الربح: <strong class="{{ $profitMargin >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $profitMargin }}%</strong></span>
                </div>
            </div>
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Stat: Orders -->
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

        <!-- Stat: Customers -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_customers_count') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $totalCustomers }}</h3>
            </div>
            <div class="p-3.5 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- Stat: Returns -->
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

        <!-- Stat: Exchanges -->
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

        <!-- Stat: Low Stock -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('global.admin_low_stock') }}</span>
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $lowStockCount }}</h3>
            </div>
            <div class="p-3.5 {{ $lowStockCount > 0 ? 'bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400' }} rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Financial Report Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>التقرير المالي</span>
            </h4>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">إجمالي</span>
                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/20 px-2 py-0.5 rounded">{{ (int) round($totalProductRevenue + $totalShippingCollected) }} {{ __('global.currency') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Revenue -->
            <div class="bg-green-50 dark:bg-green-950/20 rounded-xl p-4 border border-green-100 dark:border-green-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-wide">إيراد</span>
                    <span class="text-xs text-gray-400">هذا الشهر: {{ (int) round($monthlyProductRevenue) }}</span>
                </div>
                <p class="text-2xl font-extrabold text-green-700 dark:text-green-400">{{ (int) round($totalProductRevenue) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-green-600/70 dark:text-green-500/70 mt-1">إجمالي مبيعات المنتجات (بدون شحن)</p>
            </div>

            <!-- Shipping -->
            <div class="bg-amber-50 dark:bg-amber-950/20 rounded-xl p-4 border border-amber-100 dark:border-amber-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wide">مصاريف الشحن</span>
                    <span class="text-xs text-gray-400">{{ (int) round($monthlyShippingCollected) }}</span>
                </div>
                <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-400">{{ (int) round($totalShippingCollected) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-amber-600/70 dark:text-amber-500/70 mt-1">محصّل من العملاء — يُدفع لشركة الشحن</p>
            </div>

            <!-- COGS -->
            <div class="bg-orange-50 dark:bg-orange-950/20 rounded-xl p-4 border border-orange-100 dark:border-orange-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wide">تكلفة البضاعة</span>
                    <span class="text-xs text-gray-400">{{ (int) round($monthlyCosts) }}</span>
                </div>
                <p class="text-2xl font-extrabold text-orange-700 dark:text-orange-400">{{ (int) round($totalCosts) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-orange-600/70 dark:text-orange-500/70 mt-1">تكلفة شراء المنتجات من الموردين</p>
            </div>

            <!-- Other Expenses -->
            <div class="bg-red-50 dark:bg-red-950/20 rounded-xl p-4 border border-red-100 dark:border-red-900/30">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-wide">مصروفات أخرى</span>
                    <span class="text-xs text-gray-400">{{ (int) round($monthlyManualExpenses) }}</span>
                </div>
                <p class="text-2xl font-extrabold text-red-700 dark:text-red-400">{{ (int) round($totalManualExpenses) }} <span class="text-xs font-normal">{{ __('global.currency') }}</span></p>
                <p class="text-xs text-red-600/70 dark:text-red-500/70 mt-1">إيجار، رواتب، فواتير، إلخ</p>
            </div>
        </div>

        <!-- Net profit summary -->
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>صافي الربح = إيراد <span class="text-green-600 font-bold">{{ (int) round($totalProductRevenue) }}</span>
                - تكلفة <span class="text-orange-600 font-bold">{{ (int) round($totalCosts) }}</span>
                - شحن <span class="text-amber-600 font-bold">{{ (int) round($totalShippingCollected) }}</span>
                - مصروفات <span class="text-red-600 font-bold">{{ (int) round($totalManualExpenses) }}</span></span>
            </div>
            <div class="text-lg font-extrabold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                = {{ (int) round($netProfit) }} {{ __('global.currency') }}
            </div>
        </div>
    </div>

    <!-- Charts Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Weekly Revenue & Orders Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('global.admin_weekly_sales') }}</h4>
            <div class="h-80 w-full relative">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        <!-- Annual Revenue Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('global.admin_monthly_sales') }}</h4>
            <div class="h-80 w-full relative">
                <canvas id="annualChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ __('global.admin_low_stock') }}</span>
            </h4>
            <span class="text-xs text-gray-500 dark:text-gray-400">الحد الافتراضي للتنبيه: أقل من 5 قطع</span>
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
                        <td class="px-6 py-4 font-bold text-red-600">{{ (int) round($expense->amount) }} ج.م</td>
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
    document.addEventListener('DOMContentLoaded', function () {
        const weeklyRaw = @json(array_values($weeklyData));
        const monthlyRaw = @json(array_values($monthlyData));

        const weeklyLabels = weeklyRaw.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('ar-EG', { weekday: 'short', month: 'short', day: 'numeric' });
        });
        const weeklyRevenue = weeklyRaw.map(item => item.revenue);
        const weeklyCounts = weeklyRaw.map(item => item.count);

        const monthlyLabels = monthlyRaw.map(item => {
            const [year, month] = item.month.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('ar-EG', { month: 'long', year: 'numeric' });
        });
        const monthlyRevenue = monthlyRaw.map(item => item.revenue);

        const ctxWeekly = document.getElementById('weeklyChart').getContext('2d');
        new Chart(ctxWeekly, {
            type: 'line',
            data: {
                labels: weeklyLabels,
                datasets: [
                    {
                        label: '{{ __("global.admin_revenue") }} ({{ __("global.currency") }})',
                        data: weeklyRevenue,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: '{{ __("global.admin_orders_count") }}',
                        data: weeklyCounts,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Cairo', size: 12 }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: '{{ __("global.admin_revenue") }} ({{ __("global.currency") }})' },
                        grid: { color: 'rgba(156, 163, 175, 0.1)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: '{{ __("global.admin_orders_count") }}' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });

        const ctxAnnual = document.getElementById('annualChart').getContext('2d');
        new Chart(ctxAnnual, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: '{{ __("global.admin_monthly_sales") }} ({{ __("global.currency") }})',
                    data: monthlyRevenue,
                    backgroundColor: '#3b82f6',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 8,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        position: 'right',
                        grid: { color: 'rgba(156, 163, 175, 0.1)' }
                    }
                }
            }
        });
    });
</script>
@endpush
