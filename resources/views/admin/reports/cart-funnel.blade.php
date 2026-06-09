@extends('admin.layouts.app')

@section('page-title', 'قمع السلة المتروكة')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-bold">&larr; العودة للتقارير</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">معدل الهروب حسب الخطوة</h3>
        <div class="space-y-3">
            @forelse(($data['funnel'] ?? []) as $step)
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $step->step_name ?? 'خطوة ' . $step->checkout_step }}</span>
                    <span class="font-black {{ $loop->first ? 'text-emerald-600' : 'text-rose-600' }}">{{ $step->count }}</span>
                </div>
                @if(!$loop->first)
                @php $prevCount = $data['funnel'][$loop->index - 1]->count ?? 0; $drop = $prevCount > 0 ? round((1 - $step->count / $prevCount) * 100) : 0; @endphp
                @if($drop > 0)
                <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                    <div class="bg-rose-500 h-2 rounded-full" style="width: {{ $drop }}%"></div>
                </div>
                <p class="text-[10px] text-rose-500 font-bold mt-0.5">نسبة التسرب: {{ $drop }}%</p>
                @endif
                @endif
            </div>
            @empty
            <p class="text-sm text-slate-400">لا توجد بيانات</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">اتجاه آخر 7 أيام</h3>
        <div class="space-y-2">
            @forelse(($data['trend'] ?? []) as $day)
            <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm text-slate-600 dark:text-slate-400">{{ \Carbon\Carbon::parse($day->date)->format('Y-m-d') }}</span>
                <span class="text-sm font-bold {{ $day->count > 0 ? 'text-amber-600' : 'text-slate-400' }}">{{ $day->count }} عربة متروكة</span>
            </div>
            @empty
            <p class="text-sm text-slate-400">لا توجد بيانات</p>
            @endforelse
        </div>
    </div>
</div>
@endsection