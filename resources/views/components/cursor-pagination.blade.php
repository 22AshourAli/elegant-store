@props([
    'nextCursor' => null,
    'prevCursor' => null,
    'hasMore' => false,
    'label' => __('global.load_more') ?? 'تحميل المزيد',
])

@if($prevCursor || $hasMore)
    <div class="mt-6 flex items-center justify-center gap-3">
        @if($prevCursor)
            <a href="{{ request()->fullUrlWithQuery(['cursor' => $prevCursor, 'dir' => 'prev']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ __('global.previous') ?? 'السابق' }}
            </a>
        @endif

        @if($hasMore && $nextCursor)
            <a href="{{ request()->fullUrlWithQuery(['cursor' => $nextCursor]) }}"
               class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all text-sm font-medium shadow-sm shadow-indigo-600/20">
                {{ $label }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endif
    </div>
@endif
