@extends('layouts.store')

@section('content')
<!-- Hero Slider -->
<section x-data="{
    currentSlide: 0,
    autoplayInterval: null,
    isPaused: false,
    slides: {{ Js::from($slides) }},
    init() { this.startAutoplay(); },
    startAutoplay() {
        this.autoplayInterval = setInterval(() => {
            if (!this.isPaused) this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        }, 5000);
    },
    stopAutoplay() { if (this.autoplayInterval) clearInterval(this.autoplayInterval); },
    goToSlide(index) { this.currentSlide = index; }
}"
x-init="init()"
@mouseenter="isPaused = true"
@mouseleave="isPaused = false"
class="relative min-h-[500px] md:min-h-[600px] overflow-hidden">

    <template x-for="(slide, index) in slides" :key="index">
        <div x-show="currentSlide === index"
             x-transition:enter.opacity.duration.700
             x-transition:leave.opacity.duration.300
             class="absolute inset-0 flex items-center text-white"
             :class="'bg-gradient-to-r ' + slide.gradient">

            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <polygon fill="currentColor" points="0,100 100,0 100,100"/>
                </svg>
            </div>

            <div class="container mx-auto px-4 relative z-10 text-center">
                <h1 class="hero-title text-4xl md:text-6xl font-extrabold mb-6 tracking-tight" x-text="slide.title"></h1>
                <p class="hero-desc text-xl md:text-2xl mb-10 max-w-2xl mx-auto font-light text-white/90" x-text="slide.description"></p>
                <a :href="slide.link" class="hero-btn inline-block bg-white text-indigo-600 px-8 py-3 rounded-full font-bold hover:bg-indigo-50 hover:scale-105 transition-all shadow-lg" x-text="slide.cta"></a>
            </div>
        </div>
    </template>

    <div class="absolute bottom-8 left-0 right-0 flex justify-center gap-3 z-20">
        <template x-for="(slide, index) in slides" :key="'dot-' + index">
            <button @click="goToSlide(index)"
                    :class="currentSlide === index ? 'bg-white w-8' : 'bg-white/50 w-3'"
                    class="h-3 rounded-full transition-all duration-500 hover:bg-white focus:outline-none focus:ring-2 focus:ring-white/50">
            </button>
        </template>
    </div>
</section>

<!-- Categories -->
<section class="reveal container mx-auto px-4 py-16">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ __('global.shop_by_category') }}</h2>
        <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ __('global.view_all') }} &larr;</a>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        @foreach($categories as $cat)
            <a href="{{ route('shop.category', $cat->slug) }}" class="group relative bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 aspect-square flex items-center justify-center p-6 text-center">
                <!-- Optional: Add an icon or background image here based on category -->
                <div class="relative z-10">
                    <span class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $cat->name }}</span>
                    @if($cat->children->count() > 0)
                        <span class="block text-sm text-gray-500 mt-2">{{ $cat->children->count() }} {{ __('global.sub_categories') }}</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</section>

<!-- Filter Bar -->
<div x-data="productFilter()" x-init="init()" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-[73px] z-40 shadow-sm">
    <div class="container mx-auto px-4 py-3">

        <!-- Desktop -->
        <div class="hidden md:flex items-center gap-4 flex-wrap">
            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ __('global.filter_title') }}</span>

            <select x-model="filters.category_id" @change="applyFilters()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('global.filter_all_categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select x-model="filters.sort" @change="applyFilters()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('global.filter_sort_newest') }}</option>
                <option value="price_asc">{{ __('global.filter_sort_price_low') }}</option>
                <option value="price_desc">{{ __('global.filter_sort_price_high') }}</option>
                <option value="name_az">{{ __('global.filter_sort_name_az') }}</option>
            </select>

            <input type="number" x-model="filters.min_price" placeholder="{{ __('global.filter_min_price') }}" class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">

            <input type="number" x-model="filters.max_price" placeholder="{{ __('global.filter_max_price') }}" class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">

            <button @click="applyFilters()" :disabled="loading" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition disabled:opacity-50 flex items-center gap-2">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-show="!loading">{{ __('global.filter_apply') }}</span>
            </button>

            <button @click="resetFilters()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 text-sm font-medium transition">
                {{ __('global.filter_reset') }}
            </button>
        </div>

        <!-- Mobile -->
        <div class="md:hidden flex items-center justify-between">
            <button @click="mobileOpen = true" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-sm font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                {{ __('global.filter_title') }}
            </button>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-50 md:hidden">
            <div @click="mobileOpen = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} w-80 max-w-full bg-white dark:bg-gray-800 shadow-2xl z-10">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ __('global.filter_title') }}</h3>
                    <button @click="mobileOpen = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-4 space-y-4 overflow-y-auto max-h-[calc(100vh-140px)]">
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700 dark:text-gray-300">{{ __('global.filter_category') }}</label>
                        <select x-model="filters.category_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('global.filter_all_categories') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700 dark:text-gray-300">{{ __('global.filter_sort') }}</label>
                        <select x-model="filters.sort" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('global.filter_sort_newest') }}</option>
                            <option value="price_asc">{{ __('global.filter_sort_price_low') }}</option>
                            <option value="price_desc">{{ __('global.filter_sort_price_high') }}</option>
                            <option value="name_az">{{ __('global.filter_sort_name_az') }}</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold mb-1 text-gray-700 dark:text-gray-300">{{ __('global.filter_min_price') }}</label>
                            <input type="number" x-model="filters.min_price" placeholder="0" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold mb-1 text-gray-700 dark:text-gray-300">{{ __('global.filter_max_price') }}</label>
                            <input type="number" x-model="filters.max_price" placeholder="1000" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button @click="applyFilters()" :disabled="loading" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span x-show="!loading">{{ __('global.filter_apply') }}</span>
                        </button>
                        <button @click="resetFilters()" class="flex-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2.5 rounded-lg text-sm font-bold transition hover:bg-gray-50 dark:hover:bg-gray-700">
                            {{ __('global.filter_reset') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Products Section -->
<section id="featured" class="reveal bg-gray-50 dark:bg-gray-800/50 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('global.featured_products') }}</h2>
            <div class="w-24 h-1 bg-indigo-600 mx-auto rounded"></div>
        </div>

        <div id="product-grid">
            @include('shop.partials.product-grid', ['products' => $products])
        </div>
    </div>
</section>

<!-- Features/Benefits Section -->
<section class="reveal container mx-auto px-4 py-16 border-t border-gray-200 dark:border-gray-800">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div class="p-6">
            <div class="w-16 h-16 mx-auto bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            </div>
            <h3 class="text-xl font-bold mb-2">{{ __('global.quality_guaranteed') }}</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ __('global.quality_desc') }}</p>
        </div>
        <div class="p-6">
            <div class="w-16 h-16 mx-auto bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold mb-2">{{ __('global.fast_delivery') }}</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ __('global.delivery_desc') }}</p>
        </div>
        <div class="p-6">
            <div class="w-16 h-16 mx-auto bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <h3 class="text-xl font-bold mb-2">{{ __('global.secure_payment') }}</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ __('global.payment_desc') }}</p>
        </div>
    </div>
</section>
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productFilter', () => ({
            filters: {
                category_id: '',
                sort: '',
                min_price: '',
                max_price: '',
            },
            mobileOpen: false,
            loading: false,

            init() {
                const params = new URLSearchParams(window.location.search);
                this.filters.category_id = params.get('category_id') || '';
                this.filters.sort = params.get('sort') || '';
                this.filters.min_price = params.get('min_price') || '';
                this.filters.max_price = params.get('max_price') || '';
            },

            applyFilters() {
                this.loading = true;
                const params = new URLSearchParams();
                Object.entries(this.filters).forEach(([key, value]) => {
                    if (value) params.append(key, value);
                });

                fetch(`/?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById('product-grid').innerHTML = data.html;
                        this.loading = false;
                        this.mobileOpen = false;
                        history.pushState({}, '', `/?${params.toString()}`);
                    })
                    .catch(() => {
                        this.loading = false;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __('global.filter_error') }}', type: 'error' } }));
                    });
            },

            resetFilters() {
                this.filters = { category_id: '', sort: '', min_price: '', max_price: '' };
                this.applyFilters();
            }
        }));
    });
</script>
@endpush
@endsection
