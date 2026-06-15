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
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center gap-0.5 sm:gap-1 py-8">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 text-gray-400 dark:text-gray-600 cursor-not-allowed rounded-lg">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        @endif

        <a href="{{ $paginator->url(1) }}" class="inline-flex items-center justify-center min-w-[1.75rem] sm:min-w-[2.5rem] w-7 h-7 sm:w-9 sm:h-9 text-xs sm:text-sm font-medium rounded-lg transition-all duration-200 {{ $current === 1 ? 'text-white bg-indigo-600 dark:bg-indigo-700 border-2 border-indigo-600 dark:border-indigo-700 shadow-md' : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 dark:hover:text-indigo-400 border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:scale-105' }}" aria-label="Page 1">1</a>

        @if ($showStartEllipsis)
            <span class="inline-flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 text-xs sm:text-sm text-gray-400 dark:text-gray-500">...</span>
        @endif

        @for ($page = $start; $page <= $end; $page++)
            <a href="{{ $paginator->url($page) }}" class="inline-flex items-center justify-center min-w-[1.75rem] sm:min-w-[2.5rem] w-7 h-7 sm:w-9 sm:h-9 text-xs sm:text-sm font-medium rounded-lg transition-all duration-200 {{ $page === $current ? 'text-white bg-indigo-600 dark:bg-indigo-700 border-2 border-indigo-600 dark:border-indigo-700 shadow-md' : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 dark:hover:text-indigo-400 border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:scale-105' }}" aria-label="Page {{ $page }}">{{ $page }}</a>
        @endfor

        @if ($showEndEllipsis)
            <span class="inline-flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 text-xs sm:text-sm text-gray-400 dark:text-gray-500">...</span>
        @endif

        @if ($last > 1)
            <a href="{{ $paginator->url($last) }}" class="inline-flex items-center justify-center min-w-[1.75rem] sm:min-w-[2.5rem] w-7 h-7 sm:w-9 sm:h-9 text-xs sm:text-sm font-medium rounded-lg transition-all duration-200 {{ $current === $last ? 'text-white bg-indigo-600 dark:bg-indigo-700 border-2 border-indigo-600 dark:border-indigo-700 shadow-md' : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 dark:hover:text-indigo-400 border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:scale-105' }}" aria-label="Page {{ $last }}">{{ $last }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-7 h-7 sm:w-9 sm:h-9 text-gray-400 dark:text-gray-600 cursor-not-allowed rounded-lg">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif
    </nav>
@endif
