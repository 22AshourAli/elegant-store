@php
    $inWish = in_array($product->id, $wishlistIds ?? []);
    $inCart = in_array($product->id, $cartProductIds ?? []);
    $firstVariant = $product->variants->first();
    $hasStock = $firstVariant && $firstVariant->total_stock > 0;
    $isOutOfStock = $product->variants->isNotEmpty() && $product->variants->every(fn($v) => $v->total_stock <= 0);
    $allColors = $product->variants->pluck('color')->unique()->filter()->values();
    $allSizes = $product->variants->pluck('size')->unique()->filter()->values();
    $hasColorVar = $allColors->isNotEmpty();
    $hasSizeVar = $allSizes->isNotEmpty();
@endphp
<article class="group product-card relative overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:glow-indigo"
     x-data="{ inWishlist: {{ $inWish ? 'true' : 'false' }}, inCart: {{ $inCart ? 'true' : 'false' }}, removed: false, addingToCart: false }"
     x-show="!removed"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95">
    
    <a href="{{ route('shop.product', $product->slug) }}" class="block relative overflow-hidden aspect-[4/5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 rounded-t-2xl" aria-label="{{ $product->name }} details" dir="auto">
        @php
            $image = $firstVariant?->imageUrl() ?: $product->firstImageUrl();
            $discountPct = $product->isOnSale ? round((1 - $product->current_price / $product->base_price) * 100) : 0;
            $descText = $product->description ? Str::limit(strip_tags($product->description), 60) : __('High-end luxury product');
        @endphp
        <img src="{{ $image }}" alt="{{ $product->name }} - {{ $descText }} - Elegant Store" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 ease-out transform-gpu group-hover:scale-110 group-hover:brightness-105"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="absolute inset-0 bg-slate-100 dark:bg-slate-800 items-center justify-center" style="display:none">
            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>

        @if($product->isOnSale)
            <div class="absolute top-3 right-3 z-10 bg-gradient-to-br from-emerald-500 to-teal-600 text-white text-[11px] sm:text-xs font-black px-2.5 py-1.5 rounded-lg shadow-[0_4px_20px_rgba(16,185,129,0.5)] border border-white/20 flex items-center gap-1.5 animate-pulse">
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                <span>-{{ $discountPct }}%</span>
            </div>
        @endif

        @if($isOutOfStock)
            <div class="absolute top-3 left-3 bg-slate-900/70 backdrop-blur-sm text-white text-[10px] sm:text-xs font-bold px-2.5 py-1 rounded-full z-10 border border-white/20">
                {{ __('global.out_of_stock') }}
            </div>
        @endif

        <!-- Premium hover overlay with backdrop blur -->
        <div class="absolute inset-0 bg-slate-950/20 dark:bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-all duration-500 backdrop-blur-[2px] flex items-center justify-center">
            <span class="bg-white/95 dark:bg-surface-dark/95 text-slate-900 dark:text-slate-100 p-3 rounded-full shadow-xl hover:bg-brand-primary dark:hover:bg-accent hover:text-white dark:hover:text-white transition-all duration-300 transform scale-90 group-hover:scale-100 hover:scale-110">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </span>
        </div>
    </a>
    
    <div class="p-3 pt-2 text-start bg-white dark:bg-surface-dark">
        @if($hasColorVar || $hasSizeVar)
        <div class="flex items-center gap-2.5 mb-1.5" dir="auto">
            @if($hasColorVar)
            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400 dark:text-slate-500" title="{{ $allColors->implode(' · ') }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                <span>{{ $allColors->count() }}</span>
            </span>
            @endif
            @if($hasSizeVar)
            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400 dark:text-slate-500">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>{{ $allSizes->count() }}</span>
            </span>
            @endif
        </div>
        @endif
        <div class="flex items-start justify-between gap-1">
            <h3 class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-800 dark:text-slate-100 mb-1.5 line-clamp-2">
                <a href="{{ route('shop.product', $product->slug) }}" class="hover:text-brand-primary dark:hover:text-accent text-slate-900 dark:text-white transition-colors duration-300 inline-block focus-visible:outline-none focus-visible:underline" dir="auto">
                    {{ $product->name }}
                </a>
            </h3>
            @if($hasStock && !$isOutOfStock)
            <span class="flex-shrink-0 inline-flex items-center gap-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5" title="{{ __('global.in_stock') }}">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="hidden sm:inline">{{ __('global.in_stock') }}</span>
            </span>
            @endif
        </div>

        <div class="mt-2">
            @php
                $locale = app()->getLocale();
                $fmtNum = fn($n) => $locale === 'ar' ? str_replace(['0','1','2','3','4','5','6','7','8','9'], ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], (string)(int)round($n)) : (int)round($n);
            @endphp
            <div class="flex items-baseline gap-1.5 sm:gap-2">
                @if($product->isOnSale)
                    <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $fmtNum($product->current_price) }} {{ __('global.currency') }}</span>
                    <span class="text-[10px] sm:text-xs text-slate-400 dark:text-slate-500 line-through font-bold">{{ $fmtNum($product->base_price) }} {{ __('global.currency') }}</span>
                @else
                    <span class="text-sm sm:text-base font-black text-brand-primary dark:text-accent tracking-tight">{{ $fmtNum($product->current_price) }} {{ __('global.currency') }}</span>
                @endif
            </div>
            @if($product->isOnSale)
                <p class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 font-bold mt-0.5 flex items-center gap-0.5" dir="auto">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'وفر' : 'Save' }} {{ $fmtNum($product->base_price - $product->current_price) }} {{ __('global.currency') }}</span>
                </p>
            @endif
        </div>

        <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 mb-0.5 line-clamp-1 leading-relaxed" dir="auto">
            {{ $descText }}
        </p>

        <div class="flex items-center gap-2 mt-2 pt-2 border-t border-slate-100 dark:border-slate-800/60">
            <a href="{{ route('shop.product', $product->slug) }}" class="flex-1 text-center text-xs sm:text-sm bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white font-extrabold py-2 rounded-xl shadow-sm hover:shadow-[0_4px_12px_rgba(79,70,229,0.25)] active:scale-[0.98] transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                {{ __('global.view_details') }}
            </a>
            @if($hasStock)
            <button type="button" @click="
                if (addingToCart) return; addingToCart = true;
                fetch('{{ route('cart.add', $firstVariant) }}', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => { inCart = true; addingToCart = false; window.dispatchEvent(new CustomEvent('toast', { detail: { message: d.message, type: 'success' } })); window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: d.cartCount } })); })
                    .catch(() => { addingToCart = false; window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __('global.error_adding_to_cart') }}', type: 'error' } })); });
            " class="p-2 rounded-xl border transition-all duration-300 hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary cursor-pointer"
            :class="inCart ? 'bg-brand-primary/10 dark:bg-accent/15 border-brand-primary/30 dark:border-accent/30 text-brand-primary dark:text-accent shadow-sm' : 'border-slate-200/60 dark:border-slate-800/80 text-slate-400 hover:text-brand-primary dark:hover:text-accent hover:border-brand-primary/40 dark:hover:border-accent/40 hover:shadow-sm'"
            :title="addingToCart ? '{{ __('global.adding') }}...' : (inCart ? '{{ __('global.added_to_cart') }}' : '{{ __('global.add_to_cart') }}')"
            :aria-label="addingToCart ? '{{ __('global.adding') }}...' : (inCart ? '{{ __('global.added_to_cart') }}' : '{{ __('global.add_to_cart') }}')">
                <svg x-show="!addingToCart" class="w-4 sm:w-4.5 h-4 sm:h-4.5 transition-transform duration-300"
                    :fill="inCart ? 'currentColor' : 'none'"
                    stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <svg x-show="addingToCart" class="w-4 sm:w-4.5 h-4 sm:h-4.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </button>
            @endif
            @auth
            <button type="button" @click="
                fetch('{{ route('wishlist.toggle', $product->id) }}', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => { inWishlist = d.added; if(!d.added && {{ ($hideOnRemove ?? false) ? 'true' : 'false' }}) removed = true; window.dispatchEvent(new CustomEvent('toast', { detail: { message: d.message, type: d.added ? 'success' : 'info' } })); window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: d.count } })); })
                    .catch(() => window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __('global.login_required') }}', type: 'error' } })));
            " class="p-2 rounded-xl border transition-all duration-300 hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary cursor-pointer"
            :class="inWishlist ? 'bg-red-500/10 dark:bg-red-950/20 border-red-500/30 dark:border-red-800/80 text-red-500 shadow-sm' : 'border-slate-200/60 dark:border-slate-800/80 text-slate-400 hover:text-red-500 hover:border-red-500/40 dark:hover:border-red-500/40 hover:shadow-sm'"
            :title="inWishlist ? '{{ __('global.remove_from_wishlist') }}' : '{{ __('global.add_to_wishlist') }}'"
            :aria-label="inWishlist ? '{{ __('global.remove_from_wishlist') }}' : '{{ __('global.add_to_wishlist') }}'">
                <svg class="w-4 sm:w-4.5 h-4 sm:h-4.5 transition-transform duration-300" viewBox="0 0 24 24"
                    :fill="inWishlist ? 'currentColor' : 'none'"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </button>
            @else
            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="p-2 rounded-xl border border-slate-200/60 dark:border-slate-800/80 text-slate-400 hover:text-red-500 hover:border-red-500/40 dark:hover:border-red-500/40 transition-all duration-300 hover:scale-105 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                title="{{ __('global.add_to_wishlist') }}"
                aria-label="{{ __('global.add_to_wishlist') }}">
                <svg class="w-4 sm:w-4.5 h-4 sm:h-4.5 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </a>
            @endauth
        </div>
    </div>
</article>
