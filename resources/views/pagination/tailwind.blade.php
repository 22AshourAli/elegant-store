@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $window = 2;
        $start = max(2, $current - $window);
        $end = min($last - 1, $current + $window);
        $showStartEllipsis = $start > 2;
        $showEndEllipsis = $end < $last - 1;
    @endphp
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center gap-3 sm:gap-4 py-10">
        <div class="flex items-center gap-0.5 sm:gap-1.5 bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl rounded-2xl p-1.5 sm:p-2 shadow-sm border border-slate-100/60 dark:border-slate-800/60">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 text-slate-300 dark:text-slate-600 cursor-not-allowed rounded-xl">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 text-slate-500 dark:text-slate-400 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-indigo-100/60 dark:hover:from-indigo-950/40 dark:hover:to-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-xl border border-transparent hover:border-indigo-200/50 dark:hover:border-indigo-800/50 transition-all duration-200 hover:scale-105 active:scale-95">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            <a href="{{ $paginator->url(1) }}" class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.5rem] w-8 h-8 sm:w-10 sm:h-10 text-xs sm:text-sm font-bold rounded-xl transition-all duration-200 {{ $current === 1 ? 'bg-gradient-to-br from-indigo-600 to-indigo-700 dark:from-indigo-500 dark:to-indigo-600 text-white shadow-[0_2px_12px_rgba(79,70,229,0.3)] dark:shadow-[0_2px_12px_rgba(99,102,241,0.25)] scale-100' : 'text-slate-600 dark:text-slate-300 bg-white/80 dark:bg-slate-800/80 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-indigo-100/60 dark:hover:from-indigo-950/40 dark:hover:to-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200/60 dark:border-slate-700/60 hover:border-indigo-200/50 dark:hover:border-indigo-800/50 hover:scale-105 active:scale-95' }}" aria-label="Page 1">1</a>

            @if ($showStartEllipsis)
                <span class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 text-xs sm:text-sm font-bold text-slate-400 dark:text-slate-500 tracking-widest select-none">•••</span>
            @endif

            @for ($page = $start; $page <= $end; $page++)
                <a href="{{ $paginator->url($page) }}" class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.5rem] w-8 h-8 sm:w-10 sm:h-10 text-xs sm:text-sm font-bold rounded-xl transition-all duration-200 {{ $page === $current ? 'bg-gradient-to-br from-indigo-600 to-indigo-700 dark:from-indigo-500 dark:to-indigo-600 text-white shadow-[0_2px_12px_rgba(79,70,229,0.3)] dark:shadow-[0_2px_12px_rgba(99,102,241,0.25)] scale-100' : 'text-slate-600 dark:text-slate-300 bg-white/80 dark:bg-slate-800/80 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-indigo-100/60 dark:hover:from-indigo-950/40 dark:hover:to-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200/60 dark:border-slate-700/60 hover:border-indigo-200/50 dark:hover:border-indigo-800/50 hover:scale-105 active:scale-95' }}" aria-label="Page {{ $page }}">{{ $page }}</a>
            @endfor

            @if ($showEndEllipsis)
                <span class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 text-xs sm:text-sm font-bold text-slate-400 dark:text-slate-500 tracking-widest select-none">•••</span>
            @endif

            @if ($last > 1)
                <a href="{{ $paginator->url($last) }}" class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.5rem] w-8 h-8 sm:w-10 sm:h-10 text-xs sm:text-sm font-bold rounded-xl transition-all duration-200 {{ $current === $last ? 'bg-gradient-to-br from-indigo-600 to-indigo-700 dark:from-indigo-500 dark:to-indigo-600 text-white shadow-[0_2px_12px_rgba(79,70,229,0.3)] dark:shadow-[0_2px_12px_rgba(99,102,241,0.25)] scale-100' : 'text-slate-600 dark:text-slate-300 bg-white/80 dark:bg-slate-800/80 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-indigo-100/60 dark:hover:from-indigo-950/40 dark:hover:to-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200/60 dark:border-slate-700/60 hover:border-indigo-200/50 dark:hover:border-indigo-800/50 hover:scale-105 active:scale-95' }}" aria-label="Page {{ $last }}">{{ $last }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 text-slate-500 dark:text-slate-400 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-indigo-100/60 dark:hover:from-indigo-950/40 dark:hover:to-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-xl border border-transparent hover:border-indigo-200/50 dark:hover:border-indigo-800/50 transition-all duration-200 hover:scale-105 active:scale-95">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 text-slate-300 dark:text-slate-600 cursor-not-allowed rounded-xl">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        <p class="text-xs sm:text-sm text-slate-400 dark:text-slate-500 font-medium">
            <span>{{ __('global.showing') }}</span>
            <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $paginator->firstItem() }}</span>
            <span>{{ __('global.to') }}</span>
            <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $paginator->lastItem() }}</span>
            <span>{{ __('global.of') }}</span>
            <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $paginator->total() }}</span>
            <span>{{ __('global.products') }}</span>
        </p>
    </nav>
@endif
