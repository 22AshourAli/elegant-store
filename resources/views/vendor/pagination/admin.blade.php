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
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center gap-2 sm:gap-3">
        <div class="text-xs text-gray-500 dark:text-gray-400">
            @if ($paginator->firstItem())
                <span class="hidden sm:inline">{{ __('global.showing') }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->firstItem() }}</span>
                <span class="hidden sm:inline">{{ __('global.to') }}</span>
                <span class="sm:hidden">-</span>
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->lastItem() }}</span>
                <span class="hidden sm:inline">{{ __('global.of') }}</span>
                <span class="sm:hidden">/</span>
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->total() }}</span>
            @endif
        </div>

        <div class="flex items-center gap-0.5 sm:gap-1">
            @if ($paginator->onFirstPage())
                <span class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            <a href="{{ $paginator->url(1) }}" class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 rounded-lg {{ $current === 1 ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} text-xs sm:text-sm transition" aria-label="Page 1">1</a>

            @if ($showStartEllipsis)
                <span class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 text-xs sm:text-sm text-gray-400 dark:text-gray-500">...</span>
            @endif

            @for ($page = $start; $page <= $end; $page++)
                <a href="{{ $paginator->url($page) }}" class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 rounded-lg {{ $page === $current ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} text-xs sm:text-sm transition" aria-label="Page {{ $page }}">{{ $page }}</a>
            @endfor

            @if ($showEndEllipsis)
                <span class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 text-xs sm:text-sm text-gray-400 dark:text-gray-500">...</span>
            @endif

            @if ($last > 1)
                <a href="{{ $paginator->url($last) }}" class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 rounded-lg {{ $current === $last ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} text-xs sm:text-sm transition" aria-label="Page {{ $last }}">{{ $last }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
