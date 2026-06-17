@include('shop.partials.product-grid-cards')

@if($products->count() > 0)
    @php $isRtl = app()->getLocale() === 'ar'; @endphp
    <div class="mt-8 flex items-center justify-center gap-4" id="cursor-pagination">
        @if(isset($prevCursor) && $prevCursor)
            <a href="{{ request()->fullUrlWithQuery(['cursor' => $prevCursor]) }}"
               class="inline-flex items-center gap-1 px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-sm font-medium cursor-prev">
                @if($isRtl)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                @endif
                {{ __('global.previous') }}
            </a>
        @endif

        @if(isset($hasMore) && $hasMore && isset($nextCursor) && $nextCursor)
            <button onclick="loadMoreProducts()"
                    class="inline-flex items-center gap-1 px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors text-sm font-medium shadow-sm cursor-next"
                    data-cursor="{{ $nextCursor }}">
                {{ __('global.load_more') }}
                @if($isRtl)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @endif
            </button>
        @endif
    </div>

    @push('scripts')
    <script>
    function loadMoreProducts() {
        const btn = document.querySelector('.cursor-next');
        if (!btn) return;
        const cursor = btn.dataset.cursor;
        if (!cursor) return;
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> {{ __('global.loading') }}';

        const params = new URLSearchParams(window.location.search);
        params.set('cursor', cursor);
        params.set('ajax', '1');

        fetch('/load-more?' + params.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(d => {
            const grid = document.querySelector('#product-grid .product-grid');
            if (grid && d.html) {
                const temp = document.createElement('div');
                temp.innerHTML = d.html;
                const newCards = temp.querySelector('.product-grid');
                if (newCards) {
                    grid.insertAdjacentHTML('beforeend', newCards.innerHTML);
                } else {
                    grid.insertAdjacentHTML('beforeend', d.html);
                }
            }

            if (d.has_more && d.next_cursor) {
                btn.dataset.cursor = d.next_cursor;
                btn.disabled = false;
                btn.innerHTML = '{{ __('global.load_more') }} <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            } else {
                btn.remove();
            }

            if (window.Alpine && typeof Alpine.initTree === 'function') {
                Alpine.initTree(btn.parentElement);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '{{ __('global.load_more') }} <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __("global.load_error") }}', type: 'error' } }));
        });
    }
    </script>
    @endpush
@else
    <div class="text-center text-slate-500 py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        {{ __('global.filter_no_results') }}
    </div>
@endif
