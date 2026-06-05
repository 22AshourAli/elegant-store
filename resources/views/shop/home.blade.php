@extends('layouts.store')

@section('content')
<!-- Hero Slider Section -->
<section x-data="{
    currentSlide: 0,
    interval: null,
    paused: false,
    slides: {{ Js::from($slides) }},
    touchStartX: 0,
    touchEndX: 0,
    start() { this.interval = setInterval(() => { if (!this.paused) this.next(); }, 5500); },
    next() { this.currentSlide = (this.currentSlide + 1) % this.slides.length; },
    prev() { this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length; },
    go(i) { this.currentSlide = i; },
    handleTouchStart(e) { this.touchStartX = e.changedTouches[0].screenX; },
    handleTouchEnd(e) {
        this.touchEndX = e.changedTouches[0].screenX;
        const threshold = 50;
        if (this.touchStartX - this.touchEndX > threshold) {
            if ('{{ app()->getLocale() }}' === 'ar') this.prev(); else this.next();
        } else if (this.touchEndX - this.touchStartX > threshold) {
            if ('{{ app()->getLocale() }}' === 'ar') this.next(); else this.prev();
        }
    }
}" x-init="start()"
   @mouseenter="paused = true" @mouseleave="paused = false"
   @keydown.left="prev()" @keydown.right="next()"
   @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)"
   tabindex="0"
   aria-label="Promotional Carousel"
   class="relative overflow-hidden bg-gradient-to-br from-slate-100 via-indigo-50/20 to-purple-50/30 dark:bg-gradient-to-br dark:from-[#0b0f19] dark:to-[#030712] min-h-[240px] sm:min-h-[380px] md:min-h-[500px] lg:min-h-[580px] rounded-3xl mx-2 sm:mx-4 my-4 shadow-2xl border border-slate-200/50 dark:border-slate-800/80 focus-visible:ring-2 focus-visible:ring-brand-primary">

    <template x-for="(slide, i) in slides" :key="i">
        <div x-show="currentSlide === i"
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 flex items-center"
             :class="slide.gradient">
            <div class="container relative z-10 px-8 sm:px-16">
                <div class="max-w-2xl text-start me-auto">
                    <p class="text-brand-primary dark:text-accent text-xs sm:text-sm font-bold tracking-[0.2em] uppercase mb-2" x-text="slide.subtitle"></p>
                    <h1 class="anim-hero-title text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 dark:text-transparent dark:bg-clip-text dark:bg-gradient-to-r dark:from-white dark:via-slate-100 dark:to-indigo-200 leading-tight mb-3 tracking-tight" x-text="slide.title"></h1>
                    <p class="anim-hero-desc text-xs sm:text-base md:text-lg text-slate-700/80 dark:text-white/80 mb-6 sm:mb-10 leading-relaxed font-medium" x-text="slide.description"></p>
                    <a :href="slide.link" class="anim-hero-btn inline-flex items-center gap-2.5 bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white px-5 sm:px-8 py-2.5 sm:py-3.5 rounded-full font-bold text-xs sm:text-sm shadow-xl hover:shadow-[0_10px_30px_rgba(79,70,229,0.3)] transition-all hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-brand-primary">
                        <span x-text="slide.cta"></span>
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
            <div class="absolute inset-0 opacity-10 dark:opacity-20 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-brand-primary/20 dark:from-accent/40 via-transparent to-transparent"></div>
        </div>
    </template>

    <button @click="prev()" aria-label="Previous Slide" class="hidden sm:flex absolute top-1/2 -translate-y-1/2 start-4 lg:start-8 w-11 h-11 rounded-full bg-white/20 dark:bg-slate-900/40 backdrop-blur-lg hover:bg-white/35 dark:hover:bg-slate-800/60 text-slate-800 dark:text-slate-100 items-center justify-center transition-all hover:scale-115 hover:shadow-[0_0_15px_rgba(79,70,229,0.25)] dark:hover:shadow-[0_0_20px_rgba(139,92,246,0.3)] z-20 focus:outline-none focus:ring-2 focus:ring-brand-primary active:scale-95 border border-white/25 dark:border-white/5 animate-pulse-soft">
        <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button @click="next()" aria-label="Next Slide" class="hidden sm:flex absolute top-1/2 -translate-y-1/2 end-4 lg:end-8 w-11 h-11 rounded-full bg-white/20 dark:bg-slate-900/40 backdrop-blur-lg hover:bg-white/35 dark:hover:bg-slate-800/60 text-slate-800 dark:text-slate-100 items-center justify-center transition-all hover:scale-115 hover:shadow-[0_0_15px_rgba(79,70,229,0.25)] dark:hover:shadow-[0_0_20px_rgba(139,92,246,0.3)] z-20 focus:outline-none focus:ring-2 focus:ring-brand-primary active:scale-95 border border-white/25 dark:border-white/5 animate-pulse-soft">
        <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </button>

    <div class="absolute bottom-5 sm:bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
        <template x-for="(slide, i) in slides" :key="'d'+i">
            <button @click="go(i)" :aria-label="'Go to slide ' + (i+1)" :class="currentSlide === i ? 'bg-brand-primary dark:bg-white w-6 sm:w-8 scale-100' : 'bg-gray-400/50 dark:bg-white/30 w-1.5 sm:w-2 scale-75 hover:bg-gray-500/70 dark:hover:bg-white/50'" class="h-1.5 sm:h-2 rounded-full transition-all duration-500 focus:outline-none"></button>
        </template>
    </div>
</section>

<!-- Stats Bar Section -->
<section class="anim-fade-up container pt-4 sm:pt-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="glass-premium dark:bg-slate-900/60 rounded-2xl p-4 sm:p-6 text-center border border-slate-200 dark:border-slate-800/60 shadow-sm hover:shadow-md hover:border-luxury-gold/30 dark:hover:border-luxury-gold/45 hover:scale-[1.03] transition-all duration-300">
            <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto bg-indigo-500/10 dark:bg-brand-primary/10 text-indigo-600 dark:text-indigo-350 rounded-xl flex items-center justify-center mb-3 border border-indigo-500/20 dark:border-brand-primary/20">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
            <div class="text-xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $products->total() ?? 0 }}+</div>
            <div class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 font-bold uppercase tracking-wider">{{ __('global.product') }}</div>
        </div>
        <div class="glass-premium dark:bg-slate-900/60 rounded-2xl p-4 sm:p-6 text-center border border-slate-200 dark:border-slate-800/60 shadow-sm hover:shadow-md hover:border-luxury-gold/30 dark:hover:border-luxury-gold/45 hover:scale-[1.03] transition-all duration-300">
            <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto bg-emerald-500/10 dark:bg-emerald-550/10 text-emerald-600 dark:text-emerald-350 rounded-xl flex items-center justify-center mb-3 border border-emerald-500/20 dark:border-emerald-900/20">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="text-xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">100%</div>
            <div class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 font-bold uppercase tracking-wider">{{ __('global.quality_guaranteed') }}</div>
        </div>
        <div class="glass-premium dark:bg-slate-900/60 rounded-2xl p-4 sm:p-6 text-center border border-slate-200 dark:border-slate-800/60 shadow-sm hover:shadow-md hover:border-luxury-gold/30 dark:hover:border-luxury-gold/45 hover:scale-[1.03] transition-all duration-300">
            <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto bg-amber-500/10 dark:bg-luxury-gold/10 text-amber-650 dark:text-luxury-gold rounded-xl flex items-center justify-center mb-3 border border-amber-500/20 dark:border-luxury-gold/20">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="text-xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('global.fast_delivery') }}</div>
            <div class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 font-bold uppercase tracking-wider">{{ __('global.delivery_desc') }}</div>
        </div>
        <div class="glass-premium dark:bg-slate-900/60 rounded-2xl p-4 sm:p-6 text-center border border-slate-200 dark:border-slate-800/60 shadow-sm hover:shadow-md hover:border-luxury-gold/30 dark:hover:border-luxury-gold/45 hover:scale-[1.03] transition-all duration-300">
            <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto bg-rose-500/10 dark:bg-rose-950/15 text-rose-600 dark:text-rose-350 rounded-xl flex items-center justify-center mb-3 border border-rose-500/20 dark:border-rose-900/20">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="text-xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('global.secure_payment') }}</div>
            <div class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 font-bold uppercase tracking-wider">{{ __('global.payment_desc') }}</div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="anim-fade-up container py-12 sm:py-16 md:py-20">
    <div class="flex items-center justify-between mb-8 sm:mb-10">
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white relative inline-block">
            {{ __('global.shop_by_category') }}
            <span class="absolute bottom-[-6px] start-0 w-12 h-1 bg-brand-primary dark:bg-accent rounded-full"></span>
        </h2>
    </div>
    <div class="stagger grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
        @foreach($categories as $cat)
            @if(is_object($cat) && isset($cat->slug))
                <a href="{{ route('shop.category', $cat->slug) }}"
                   class="group relative bg-white dark:bg-[#0b0f19] rounded-2xl overflow-hidden border border-slate-200/50 dark:border-slate-900/80 hover:border-luxury-gold/30 dark:hover:border-accent/40 aspect-[4/3] flex items-center justify-center p-5 text-center shadow-sm hover:shadow-[0_20px_40px_-15px_rgba(79,70,229,0.15)] dark:hover:shadow-[0_20px_45px_-10px_rgba(0,0,0,0.5)] transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/0 to-accent/0 group-hover:from-brand-primary/5 group-hover:to-accent/10 transition-all duration-500"></div>
                    <div class="relative z-10 flex flex-col items-center">
                        <span class="text-base sm:text-lg font-extrabold text-slate-800 dark:text-slate-100 group-hover:text-brand-primary dark:group-hover:text-accent transition-colors block line-clamp-2">{{ $cat->name }}</span>
                        @if($cat->children->count() > 0)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-brand-primary/80 dark:text-luxury-gold/90 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200/30 dark:border-luxury-gold/20 px-2.5 py-0.5 rounded-full mt-2.5 transition-all group-hover:scale-105">{{ $cat->children->count() }} {{ __('global.sub_categories') }}</span>
                        @endif
                    </div>
                </a>
            @endif
        @endforeach
    </div>
</section>

<!-- Filter Bar -->
<div x-data="productFilter()" x-init="init()" class="sticky top-12 sm:top-14 lg:top-20 z-40 bg-white/80 dark:bg-[#030712]/70 nav-blur border-b border-slate-250/30 dark:border-slate-900/60 shadow-[0_4px_30px_rgba(0,0,0,0.02)] transition-all duration-300">
    <div class="container py-2.5 sm:py-3">
        <div class="hidden md:flex items-center gap-2 lg:gap-3 flex-wrap">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-450">{{ __('global.filter_title') }}</span>
            <select x-model="filters.category_id" @change="applyFilters()" class="border border-slate-200/50 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/60 text-slate-700 dark:text-slate-355 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-brand-primary dark:focus:ring-accent focus:outline-none transition-all">
                <option value="">{{ __('global.filter_all_categories') }}</option>
                @foreach($categories as $cat)
                    @if(is_object($cat) && isset($cat->id))
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endif
                @endforeach
            </select>
            <select x-model="filters.sort" @change="applyFilters()" class="border border-slate-200/50 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/60 text-slate-700 dark:text-slate-355 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-brand-primary dark:focus:ring-accent focus:outline-none transition-all">
                <option value="">{{ __('global.filter_sort_newest') }}</option>
                <option value="price_asc">{{ __('global.filter_sort_price_low') }}</option>
                <option value="price_desc">{{ __('global.filter_sort_price_high') }}</option>
                <option value="name_az">{{ __('global.filter_sort_name_az') }}</option>
            </select>
            <input type="number" x-model="filters.min_price" placeholder="{{ __('global.filter_min_price') }}" class="w-20 border border-slate-200/50 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/60 text-slate-700 dark:text-slate-355 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-brand-primary dark:focus:ring-accent focus:outline-none transition-all">
            <input type="number" x-model="filters.max_price" placeholder="{{ __('global.filter_max_price') }}" class="w-20 border border-slate-200/50 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/60 text-slate-700 dark:text-slate-355 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-brand-primary dark:focus:ring-accent focus:outline-none transition-all">
            <button @click="applyFilters()" :disabled="loading" class="bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white px-3.5 py-1.5 rounded-lg text-xs font-extrabold transition disabled:opacity-50 flex items-center gap-1.5 shadow-md shadow-brand-primary/10 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-primary">
                <svg x-show="loading" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span x-show="!loading">{{ __('global.filter_apply') }}</span>
            </button>
            <button @click="resetFilters()" class="text-slate-400 hover:text-slate-655 dark:hover:text-slate-250 px-2 py-1.5 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-brand-primary rounded-lg">{{ __('global.filter_reset') }}</button>
        </div>
        <div class="md:hidden flex items-center justify-between">
            <button @click="mobileOpen = true" class="flex items-center gap-2 px-3 py-2 bg-brand-primary/10 text-brand-primary dark:text-accent rounded-lg text-xs font-bold hover:bg-brand-primary/20 transition-all border border-brand-primary/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>{{ __('global.filter_title') }}</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <span class="text-xs text-slate-500 font-bold" x-text="`${document.querySelectorAll('#product-grid .group').length} {{ __('global.product') }}`"></span>
        </div>
        <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-50 md:hidden animate-fadeInUp">
            <div @click="mobileOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>
            <div class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-[#0b0f19]/95 backdrop-blur-2xl rounded-t-3xl shadow-2xl z-10 flex flex-col max-h-[85vh] border-t border-slate-200/20 dark:border-slate-800/60"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-full">
                <div class="p-4 border-b border-slate-200/60 dark:border-slate-900 flex items-center justify-between shrink-0">
                    <h3 class="font-extrabold text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('global.filter_title') }}</h3>
                    <button @click="mobileOpen = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-500 transition focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:outline-none" aria-label="Close filters">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 space-y-5 overflow-y-auto flex-1 text-start">
                    <div>
                        <label class="block text-xs font-extrabold mb-2 text-slate-500 dark:text-slate-450 uppercase tracking-wider">{{ __('global.filter_sort') }}</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center justify-center p-2.5 border rounded-lg cursor-pointer transition text-xs font-bold"
                                   :class="filters.sort === '' ? 'bg-brand-primary/10 dark:bg-accent/15 border-brand-primary dark:border-accent text-brand-primary dark:text-accent scale-[1.02] shadow-sm' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/60 text-slate-700 dark:text-slate-350'">
                                <input type="radio" name="mobile_sort" value="" x-model="filters.sort" class="sr-only">
                                {{ __('global.filter_sort_newest') }}
                            </label>
                            <label class="flex items-center justify-center p-2.5 border rounded-lg cursor-pointer transition text-xs font-bold"
                                   :class="filters.sort === 'price_asc' ? 'bg-brand-primary/10 dark:bg-accent/15 border-brand-primary dark:border-accent text-brand-primary dark:text-accent scale-[1.02] shadow-sm' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/60 text-slate-700 dark:text-slate-350'">
                                <input type="radio" name="mobile_sort" value="price_asc" x-model="filters.sort" class="sr-only">
                                {{ __('global.filter_sort_price_low') }}
                            </label>
                            <label class="flex items-center justify-center p-2.5 border rounded-lg cursor-pointer transition text-xs font-bold"
                                   :class="filters.sort === 'price_desc' ? 'bg-brand-primary/10 dark:bg-accent/15 border-brand-primary dark:border-accent text-brand-primary dark:text-accent scale-[1.02] shadow-sm' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/60 text-slate-700 dark:text-slate-350'">
                                <input type="radio" name="mobile_sort" value="price_desc" x-model="filters.sort" class="sr-only">
                                {{ __('global.filter_sort_price_high') }}
                            </label>
                            <label class="flex items-center justify-center p-2.5 border rounded-lg cursor-pointer transition text-xs font-bold"
                                   :class="filters.sort === 'name_az' ? 'bg-brand-primary/10 dark:bg-accent/15 border-brand-primary dark:border-accent text-brand-primary dark:text-accent scale-[1.02] shadow-sm' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/60 text-slate-700 dark:text-slate-350'">
                                <input type="radio" name="mobile_sort" value="name_az" x-model="filters.sort" class="sr-only">
                                {{ __('global.filter_sort_name_az') }}
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold mb-2 text-slate-500 dark:text-slate-450 uppercase tracking-wider">{{ __('global.filter_category') }}</label>
                        <select x-model="filters.category_id" class="w-full border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 dark:text-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-primary focus:outline-none transition-all">
                            <option value="">{{ __('global.filter_all_categories') }}</option>
                            @foreach($categories as $cat)
                                @if(is_object($cat) && isset($cat->id))
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold mb-2 text-slate-500 dark:text-slate-450 uppercase tracking-wider">{{ __('global.filter_min_price') }} - {{ __('global.filter_max_price') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" x-model="filters.min_price" placeholder="0" class="flex-1 border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 dark:text-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-primary focus:outline-none transition-all">
                            <span class="text-slate-400 text-xs">—</span>
                            <input type="number" x-model="filters.max_price" placeholder="1000" class="flex-1 border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 dark:text-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-primary focus:outline-none transition-all">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button @click="applyFilters()" :disabled="loading" class="flex-1 bg-gradient-to-r from-brand-primary to-accent hover:from-brand-hover hover:to-accent-hover text-white px-4 py-3 rounded-xl text-sm font-extrabold transition disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-brand-primary/10 active:scale-95 focus:outline-none">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span x-show="!loading">{{ __('global.filter_apply') }}</span>
                        </button>
                        <button @click="resetFilters()" class="flex-1 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-350 px-4 py-3 rounded-xl text-sm font-extrabold transition hover:bg-slate-50 dark:hover:bg-slate-900/60 active:scale-95 focus:outline-none">{{ __('global.filter_reset') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Products -->
<section id="featured" class="anim-fade-up bg-slate-50/50 dark:bg-[#0b0f19]/30 py-8 sm:py-12 md:py-16">
    <div class="container">
        <div class="text-center mb-8 sm:mb-10">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-3">{{ __('global.featured_products') }}</h2>
            <div class="w-16 sm:w-20 h-1 bg-gradient-to-r from-transparent via-brand-primary to-transparent dark:via-accent mx-auto rounded"></div>
        </div>
        <div id="product-grid">
            @include('shop.partials.product-grid', ['products' => $products, 'wishlistIds' => $wishlistIds ?? [], 'cartProductIds' => $cartProductIds ?? []])
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.anim-fade-up, .anim-scale, .anim-slide-right, .anim-slide-left, .stagger').forEach(el => {
        if (el.getBoundingClientRect().top < window.innerHeight) {
            el.classList.add('in');
        } else {
            observer.observe(el);
        }
    });
});

document.addEventListener('alpine:init', () => {
    Alpine.data('productFilter', () => ({
        filters: { category_id: '', sort: '', min_price: '', max_price: '' },
        mobileOpen: false,
        loading: false,
        init() { const p = new URLSearchParams(window.location.search); this.filters.category_id = p.get('category_id') || ''; this.filters.sort = p.get('sort') || ''; this.filters.min_price = p.get('min_price') || ''; this.filters.max_price = p.get('max_price') || ''; },
        applyFilters() {
            this.loading = true;
            const p = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => {
                if (v !== '' && v !== null) {
                    p.append(k, v);
                }
            });
            p.append('ajax', '1');
            const requestUrl = `/?${p.toString()}`;
            const browserUrl = requestUrl.replace(/([?&])ajax=1(&|$)/, '$1').replace(/[?&]$/, '');

            fetch(requestUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(r => r.json())
                .then(d => {
                    const grid = document.getElementById('product-grid');
                    grid.innerHTML = d.html;
                    if (window.Alpine) {
                        if (typeof Alpine.discoverUninitializedComponents === 'function') {
                            Alpine.discoverUninitializedComponents(grid);
                        }
                        if (typeof Alpine.initTree === 'function') {
                            Alpine.initTree(grid);
                        }
                    }
                    this.loading = false;
                    this.mobileOpen = false;
                    history.pushState({}, '', browserUrl);
                    setTimeout(() => {
                        document.querySelectorAll('.product-grid > div').forEach((el, i) => {
                            el.style.opacity = '0';
                            el.style.transform = 'translateY(20px)';
                            setTimeout(() => {
                                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                                el.style.opacity = '1';
                                el.style.transform = 'translateY(0)';
                            }, i * 80);
                        });
                    }, 50);
                })
                .catch(() => {
                    this.loading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: "{{ __('global.filter_error') }}", type: 'error' } }));
                });
        },
        resetFilters() { this.filters = { category_id: '', sort: '', min_price: '', max_price: '' }; this.applyFilters(); }
    }));
});
</script>
@endpush
@endsection
