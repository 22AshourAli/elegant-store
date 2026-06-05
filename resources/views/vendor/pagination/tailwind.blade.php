@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 order-2 sm:order-1">
            @if ($paginator->firstItem())
                {{ __('global.showing') }}
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->firstItem() }}</span>
                {{ __('global.to') }}
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->lastItem() }}</span>
                {{ __('global.of') }}
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->total() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
        </div>

        <div class="flex items-center gap-1.5 order-1 sm:order-2">
            @if ($paginator->onFirstPage())
                <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 text-xs sm:text-sm text-gray-400 dark:text-gray-500">...</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-indigo-600 text-white text-xs sm:text-sm font-bold shadow-sm">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-600 dark:text-gray-300 text-xs sm:text-sm hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
