@extends('admin.layouts.app')

@section('page-title', 'التقارير والإحصائيات')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <a href="{{ route('admin.reports.returns') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
        <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <h3 class="font-extrabold text-lg text-slate-900 dark:text-white mb-1">تحليل المرتجعات</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">تحليل المنتجات المرتجعة حسب اللون والمقاس والسبب</p>
    </a>

    <a href="{{ route('admin.reports.aov-clv') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
        <h3 class="font-extrabold text-lg text-slate-900 dark:text-white mb-1">متوسط الطلب وقيمة العميل</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">AOV, CLV وأفضل 10 عملاء</p>
    </a>

    <a href="{{ route('admin.reports.dead-stock') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
        <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <h3 class="font-extrabold text-lg text-slate-900 dark:text-white mb-1">المخزون الراكد</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">منتجات غير مباعة منذ 30 يوماً و بطيئة الحركة</p>
    </a>

    <a href="{{ route('admin.reports.cart-funnel') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="font-extrabold text-lg text-slate-900 dark:text-white mb-1">قمع السلة المتروكة</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">تحليل خطوات الدفع ومعدل الهروب</p>
    </a>

    <a href="{{ route('admin.reports.payment-reconciliation') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        </div>
        <h3 class="font-extrabold text-lg text-slate-900 dark:text-white mb-1">تسوية المدفوعات</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">رسوم البوابة، صافي المبلغ، والتسوية</p>
    </a>

    <a href="{{ route('admin.dashboard') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex items-center justify-center">
        <div class="text-center">
            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <h3 class="font-extrabold text-lg text-slate-500 dark:text-slate-400">العودة للوحة التحكم</h3>
        </div>
    </a>
</div>
@endsection