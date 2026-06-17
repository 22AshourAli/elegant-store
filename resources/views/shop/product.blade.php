@extends('layouts.store')

@section('meta_description', Str::limit(strip_tags($product->description ?: $product->name), 160))
@section('og_title', $product->name)
@section('og_description', Str::limit(strip_tags($product->description ?: $product->name), 200))
@section('title', $product->name . ' - Elegant Store')
@section('og_image', $product->firstImageUrl())

@push('head')
    <meta property="og:type" content="product">
    <meta property="og:price:amount" content="{{ (int) round($product->current_price) }}">
    <meta property="og:price:currency" content="EGP">
    <meta property="product:availability" content="{{ $product->hasStock() ? 'in stock' : 'out of stock' }}">
@endpush

@section('content')
{{-- Breadcrumb --}}
<div class="bg-white/40 dark:bg-bg-dark/40 border-b border-slate-200/40 dark:border-slate-800/40 py-4 backdrop-blur-md">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 flex-wrap">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-brand-primary dark:hover:text-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded">{{ __('global.home') }}</a>
                </li>
                @if($product->category)
                <li class="inline-flex items-center gap-1.5">
                    <svg class="w-3 h-3 rtl:rotate-180 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-brand-primary dark:hover:text-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded">{{ $product->category->name }}</a>
                </li>
                @endif
                <li class="inline-flex items-center gap-1.5" aria-current="page">
                    <svg class="w-3 h-3 rtl:rotate-180 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="text-slate-800 dark:text-slate-200 truncate max-w-[18rem] font-bold" dir="auto">{{ $product->name }}</span>
                </li>
            </ol>
        </nav>
    </div>
</div>



<section class="container py-10 md:py-16 mb-10 overflow-x-hidden" x-data="productView(window.productViewData)" @cart-updated.window="cartLoading = false">
    <div class="grid md:grid-cols-2 gap-6 md:gap-10 lg:gap-16">

        {{-- Image Gallery --}}
        <div class="space-y-4">
            {{-- Main Image --}}
            <div class="product-main-image relative bg-white/70 dark:bg-surface-dark/50 rounded-3xl overflow-hidden shadow-lg border border-slate-200/40 dark:border-slate-800/40 aspect-[4/5] md:aspect-square group backdrop-blur-md"
                 x-data="{ imgLoaded: false, transitioning: false }"
                 x-init="$watch('currentImage', () => { imgLoaded = false; transitioning = true; }); $nextTick(() => { imgLoaded = true; transitioning = false; })">
                <img :src="currentImage" alt="{{ $product->name }}"
                     class="w-full h-full object-cover transition-all duration-500 ease-out"
                     :class="imgLoaded ? 'opacity-100 scale-100 group-hover:scale-110' : 'opacity-0 scale-95'"
                     x-on:load="imgLoaded = true; transitioning = false">
                {{-- Loading shimmer --}}
                <div x-show="!imgLoaded"
                     class="absolute inset-0 bg-gradient-to-br from-slate-100/80 to-slate-200/80 dark:from-slate-800/80 dark:to-slate-700/80 flex items-center justify-center z-20">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="w-8 h-8 text-brand-primary/60 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 animate-pulse">{{ __('global.loading') }}</span>
                    </div>
                </div>
                {{-- Badges --}}
                @if($product->isOnSale)
                <div class="absolute top-4 end-4 bg-gradient-to-r from-luxury-gold to-luxury-gold-mute text-white text-xs font-black tracking-wider px-3 py-1.5 rounded-full shadow-[0_4px_12px_rgba(212,175,55,0.3)] z-10">
                    {{ __('global.on_sale') }}
                </div>
                @endif
                {{-- Zoom hint --}}
                <div class="absolute bottom-4 start-4 bg-black/50 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center gap-1.5 z-10">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                    <span>{{ __('global.product') }}</span>
                </div>
            </div>

        </div>

        {{-- Product Details --}}
        <div class="flex flex-col text-start">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-3 leading-tight tracking-tight" dir="auto">{{ $product->name }}</h1>

            {{-- Rating --}}
            <div class="flex items-center gap-2 mb-5">
                <div class="flex text-amber-400">
                    @for($i = 0; $i < 4; $i++)
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    @endfor
                    <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">(12 {{ __('global.rating_label') }})</span>
            </div>

            {{-- Price & Countdown --}}
            <div class="flex flex-col mb-6 pb-6 border-b border-slate-200/40 dark:border-slate-800/60">
                <div class="flex items-center gap-4 flex-wrap">
                    <span x-text="formatPrice(currentPrice)" class="text-3xl sm:text-4xl font-black text-brand-primary dark:text-accent tracking-tight"
                          :class="originalPrice !== currentPrice ? 'text-emerald-600 dark:text-emerald-400' : ''"></span>
                    <span x-show="originalPrice !== currentPrice" x-cloak x-text="formatPrice(originalPrice)" class="text-base sm:text-lg text-slate-400 line-through font-bold"></span>
                    {{-- Save badge --}}
                    <span x-show="originalPrice !== currentPrice" x-cloak
                          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/30 border border-emerald-200 dark:border-emerald-800/40 text-emerald-600 dark:text-emerald-400 text-xs font-black shadow-sm">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span x-text="'{{ __('global.save') }} ' + formatNumber(originalPrice - currentPrice)"></span>
                    </span>
                </div>
                @if($product->discount_end)
                    <div class="mt-3 text-xs text-amber-700 dark:text-amber-400 font-bold flex items-center gap-1.5 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 border border-amber-200 dark:border-amber-800/40 px-3 py-2 rounded-xl w-fit max-w-full shadow-sm" x-data="{ label: '' }" x-init="
                        const end = new Date('{{ $product->discount_end->format('Y-m-d H:i:s') }}').getTime();
                        const update = () => {
                            const diff = end - Date.now();
                            if (diff <= 0) { label = ''; return; }
                            const days = Math.floor(diff / 86400000);
                            const hours = Math.floor((diff % 86400000) / 3600000);
                            const mins = Math.floor((diff % 3600000) / 60000);
                            @if(app()->getLocale() === 'ar')
                                if (days > 0) label = 'ينتهي الخصم بعد ' + days + ' يوم' + (days > 1 ? 'ًا' : '') + ' و ' + hours + ' ساعة';
                                else if (hours > 0) label = 'ينتهي الخصم بعد ' + hours + ' ساعة و ' + mins + ' دقيقة';
                                else label = 'ينتهي الخصم بعد ' + mins + ' دقيقة';
                            @else
                                if (days > 0) label = 'Ends in ' + days + ' day' + (days > 1 ? 's' : '') + ' and ' + hours + ' hour' + (hours > 1 ? 's' : '');
                                else if (hours > 0) label = 'Ends in ' + hours + ' hour' + (hours > 1 ? 's' : '') + ' and ' + mins + ' min' + (mins > 1 ? 's' : '');
                                else label = 'Ends in ' + mins + ' min' + (mins > 1 ? 's' : '');
                            @endif
                        };
                        update(); setInterval(update, 60000);
                    ">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="label"></span>
                    </div>
                @endif
            </div>

            @if(count($colors) > 0)
            {{-- Color Selection --}}
            <div class="mb-6">
                <div class="flex justify-between items-center mb-3">
                    <label class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.color') }}</label>
                    <span class="text-sm font-bold" :class="selectedColor ? 'text-brand-primary dark:text-accent' : 'text-slate-400'" x-text="selectedColor || '{{ __('global.choose_color') }}'"></span>
                </div>
                <div class="flex flex-wrap gap-4" role="group" aria-label="{{ __('global.color') }}">
                    <template x-for="color in colors" :key="color">
                        <button @click="selectColor(color)"
                                class="relative flex flex-col items-center gap-1.5 cursor-pointer focus-visible:outline-none group/color transition-all duration-300"
                                :title="color"
                                :aria-label="color"
                                :aria-pressed="selectedColor === color">
                            <span class="block w-14 h-14 sm:w-16 sm:h-16 rounded-full overflow-hidden transition-all duration-300 shadow-md"
                                  :class="selectedColor === color
                                      ? 'ring-[3px] ring-brand-primary dark:ring-accent ring-offset-2 dark:ring-offset-slate-950 shadow-[0_0_24px_rgba(79,70,229,0.4)] scale-110'
                                      : 'ring-1 ring-slate-300 dark:ring-slate-600 group-hover/color:scale-110 group-hover/color:shadow-lg group-hover/color:ring-brand-primary/50 dark:group-hover/color:ring-accent/50'">
                                <img :src="colorImages[normalize(color)] || firstImageUrl"
                                     class="w-full h-full object-cover" :alt="color"
                                     x-on:error.once="$el.src = '{{ asset('images/logo.svg') }}'">
                            </span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 truncate max-w-[4rem] transition-all duration-200"
                                  :class="selectedColor === color ? 'text-brand-primary dark:text-accent opacity-100' : 'opacity-70 group-hover/color:opacity-100'"
                                  x-text="color"></span>
                            <span x-show="selectedColor === color"
                                  class="absolute -top-1 -end-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-950 shadow-md flex items-center justify-center">
                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </button>
                    </template>
                </div>
            </div>
            @endif

            @if(count($sizes) > 0)
            {{-- Size Selection --}}
            <div class="mb-8">
                <div class="flex justify-between items-center mb-3">
                    <label class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.size') }}</label>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3" role="group" aria-label="{{ __('global.size') }}">
                    <template x-for="size in sizes" :key="size">
                        <button @click="selectedSize = size"
                                class="py-2.5 px-2 border rounded-xl text-xs font-extrabold cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary transition-all duration-300"
                                :class="selectedSize === size
                                    ? 'border-brand-primary bg-brand-primary/10 text-brand-primary dark:border-accent dark:bg-accent/15 dark:text-accent shadow-[0_0_15px_rgba(79,70,229,0.15)] scale-105'
                                    : 'border-slate-200/60 dark:border-slate-800/80 hover:border-brand-primary/40 dark:hover:border-accent/40 text-slate-700 dark:text-slate-300 bg-white/50 dark:bg-surface-dark/50 hover:shadow-sm'"
                                :aria-pressed="selectedSize === size">
                            <span x-text="size"></span>
                        </button>
                    </template>
                </div>
            </div>
            @endif

            {{-- Add to Cart Panel --}}
            <div class="bg-white/40 dark:bg-surface-dark/40 border border-slate-200/40 dark:border-slate-800/40 p-6 rounded-2xl mb-8 backdrop-blur-sm shadow-[0_8px_32px_0_rgba(31,38,135,0.03)]">
                {{-- Stock Status --}}
                <div class="flex items-center gap-2 mb-4" aria-live="polite" aria-atomic="true">
                    <div x-show="stockStatus === 'in_stock'" x-cloak class="flex items-center text-emerald-600 dark:text-emerald-400 gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="font-bold text-sm">{{ __('global.in_stock') }} (<span x-text="availableQty"></span>)</span>
                    </div>
                    <div x-show="stockStatus === 'out_of_stock'" x-cloak class="flex items-center text-red-500 dark:text-red-400 gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-bold text-sm">{{ __('global.out_of_stock') }}</span>
                    </div>
                    <div x-show="stockStatus === 'select_options'" x-cloak class="flex items-center text-amber-600 dark:text-amber-400 gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-bold text-sm">{{ __('global.select_options') }}</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    {{-- Quantity Stepper --}}
                    <div class="flex items-center border border-slate-200/60 dark:border-slate-800/80 rounded-xl bg-white/70 dark:bg-slate-950/70 overflow-hidden shadow-sm" role="group" aria-label="Quantity">
                        <button @click="if(qty > 1) qty--" aria-label="Decrease quantity" class="px-4 py-3 text-slate-500 hover:text-brand-primary hover:bg-brand-primary/5 dark:hover:bg-accent/5 focus-visible:outline-none transition-all duration-200 font-extrabold text-lg cursor-pointer">−</button>
                        <input type="number" x-model.number="qty" min="1" :max="availableQty" aria-label="Quantity" class="w-12 text-center bg-transparent border-0 focus:ring-0 p-0 font-black text-slate-900 dark:text-slate-100" readonly>
                        <button @click="if(qty < availableQty) qty++" aria-label="Increase quantity" class="px-4 py-3 text-slate-500 hover:text-brand-primary hover:bg-brand-primary/5 dark:hover:bg-accent/5 focus-visible:outline-none transition-all duration-200 font-extrabold text-lg cursor-pointer">+</button>
                    </div>

                    {{-- Add to Cart --}}
                    <button @click="addToCart"
                            :disabled="stockStatus !== 'in_stock' || cartLoading"
                            class="w-full sm:flex-1 bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white font-extrabold py-3.5 px-5 rounded-xl disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-300 active:scale-[0.97] shadow-[0_4px_20px_rgba(79,70,229,0.25)] hover:shadow-[0_8px_30px_rgba(79,70,229,0.45)] hover:-translate-y-0.5 flex justify-center items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary cursor-pointer btn-shimmer"
                            aria-label="{{ __('global.add_to_cart') }}">
                        <svg x-show="!cartLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <svg x-show="cartLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-show="!cartLoading">{{ __('global.add_to_cart') }}</span>
                        <span x-show="cartLoading">{{ __('global.adding') }}...</span>
                    </button>

                    {{-- Buy Now --}}
                    <button @click="buyNow"
                            :disabled="stockStatus !== 'in_stock' || buyLoading"
                            class="w-full sm:flex-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold py-3.5 px-5 rounded-xl disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-300 active:scale-[0.97] shadow-[0_4px_20px_rgba(16,185,129,0.2)] hover:shadow-[0_8px_30px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 flex justify-center items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 cursor-pointer"
                            aria-label="{{ __('global.buy_now') }}">
                        <svg x-show="!buyLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <svg x-show="buyLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-show="!buyLoading">{{ __('global.buy_now') }}</span>
                        <span x-show="buyLoading">{{ __('global.redirecting') }}...</span>
                    </button>
                </div>
            </div>

            {{-- Description --}}
            <div class="mt-2">
                <h3 class="font-extrabold text-sm uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">{{ __('global.description') }}</h3>
                <div class="text-slate-600 dark:text-slate-300 leading-relaxed text-sm bg-white/40 dark:bg-surface-dark/40 rounded-2xl p-6 border border-slate-200/40 dark:border-slate-800/40 backdrop-blur-sm">
                    {!! nl2br($product->description) !!}
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    window.productViewData = {
        product: @json($product),
        colors: @json($colors),
        sizes: @json($sizes),
        colorImages: @json($colorImages),
        firstImageUrl: @json($product->firstImageUrl()),
    };
</script>

@push('schema')
  @php
    $productSchema = [
      '@context' => 'https://schema.org/',
      '@type' => 'Product',
      'name' => $product->name,
      'image' => $product->firstImageUrl(),
      'description' => Str::limit(strip_tags($product->description ?? $product->name), 200),
      'sku' => $product->sku ?? '',
      'offers' => [
        '@type' => 'Offer',
        'url' => url()->current(),
        'priceCurrency' => 'EGP',
        'price' => (int) round($product->current_price),
        'availability' => $product->hasStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
      ]
    ];
  @endphp
  {!! '<script type="application/ld+json">' . json_encode($productSchema) . '</script>' !!}
@endpush
@endsection
