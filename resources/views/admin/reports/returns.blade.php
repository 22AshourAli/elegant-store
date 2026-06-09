@extends('admin.layouts.app')

@section('page-title', 'تحليل المرتجعات')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-bold">&larr; العودة للتقارير</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">حسب المنتج</h3>
        @forelse($data['by_variant'] ?? [] as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->product_name ?? '#' . $item->product_variant_id }}</span>
                <span class="text-sm font-black text-rose-600">{{ $item->return_count }} مرتجع</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">لا توجد مرتجعات مسجلة</p>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">حسب اللون</h3>
        @forelse($data['by_color'] ?? [] as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->color ?? 'بدون' }}</span>
                <span class="text-sm font-black text-rose-600">{{ $item->total }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">لا توجد بيانات</p>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">حسب المقاس</h3>
        @forelse($data['by_size'] ?? [] as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->size ?? 'بدون' }}</span>
                <span class="text-sm font-black text-rose-600">{{ $item->total }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">لا توجد بيانات</p>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">حسب سبب الإرجاع</h3>
        @forelse($data['by_reason'] ?? [] as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->reason ?? 'غير محدد' }}</span>
                <span class="text-sm font-black text-rose-600">{{ $item->total }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">لا توجد بيانات</p>
        @endforelse
    </div>
</div>
@endsection