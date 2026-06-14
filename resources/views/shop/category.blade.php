@extends('layouts.store')

@section('meta_description', Str::limit($category->description ?? $category->name, 160))
@section('og_title', $category->name)
@section('og_description', Str::limit($category->description ?? $category->name, 200))
@section('og_image', asset('images/logo.svg'))

@push('head')
    <title>{{ $category->name }} - Elegant Store</title>
    <meta property="og:type" content="website">
@endpush

@section('seo')
    {!! SEO::generate() !!}
@endsection

@section('content')
{{-- Category Header --}}
<div class="relative overflow-hidden bg-white/40 dark:bg-bg-dark/40 border-b border-slate-200/40 dark:border-slate-800/40 py-10 backdrop-blur-md">
    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-bg-light/10 dark:to-bg-dark/10 pointer-events-none"></div>
    <div class="absolute -top-24 -left-20 w-80 h-80 rounded-full bg-brand-primary/5 dark:bg-brand-primary/10 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-20 w-80 h-80 rounded-full bg-accent/5 dark:bg-accent/10 blur-3xl pointer-events-none"></div>
    
    <div class="container relative z-10">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-5">
            <ol class="inline-flex flex-wrap items-center gap-1.5 text-xs font-semibold text-slate-400 dark:text-slate-500">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-brand-primary dark:hover:text-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded">{{ __('الرئيسية') }}</a>
                </li>
                <li class="inline-flex items-center gap-1.5">
                    <svg class="w-3 h-3 rtl:rotate-180 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="#" class="hover:text-brand-primary dark:hover:text-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded">{{ __('التصنيفات') }}</a>
                </li>
                @if($category->parent)
                <li class="inline-flex items-center gap-1.5">
                    <svg class="w-3 h-3 rtl:rotate-180 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('shop.category', $category->parent->slug) }}" class="hover:text-brand-primary dark:hover:text-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded">{{ $category->parent->name }}</a>
                </li>
                @endif
                <li class="inline-flex items-center gap-1.5" aria-current="page">
                    <svg class="w-3 h-3 rtl:rotate-180 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="text-slate-700 dark:text-slate-300 font-bold">{{ $category->name }}</span>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $category->name }}</h1>

        {{-- Subcategory chips --}}
        @if($category->children->count() > 0)
            <div class="flex flex-wrap gap-2.5 mt-6 animate-fadeInUp" role="list" aria-label="Subcategories">
                @foreach($category->children as $child)
                    <a href="{{ route('shop.category', $child->slug) }}"
                       role="listitem"
                       class="px-5 py-2 bg-white/70 dark:bg-surface-dark/50 border border-slate-200/60 dark:border-slate-800/80 rounded-full text-xs font-extrabold text-slate-600 dark:text-slate-300 hover:border-brand-primary dark:hover:border-accent hover:text-brand-primary dark:hover:text-white hover:-translate-y-0.5 shadow-sm hover:shadow-[0_4px_12px_rgba(79,70,229,0.1)] transition-all duration-300 backdrop-blur-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="reveal container py-10 flex flex-col md:flex-row gap-8">

    {{-- Mobile Filter Bar --}}
    <div class="md:hidden flex items-center gap-3 mb-6" x-data="{ mobileOpen: false }">
        <button @click="mobileOpen = true"
                aria-expanded="mobileOpen"
                aria-controls="mobile-filter-drawer"
                class="flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-surface-dark border border-slate-200/60 dark:border-slate-800/80 text-slate-800 dark:text-slate-200 rounded-xl text-sm font-bold flex-1 transition-all hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary cursor-pointer">
            <svg class="w-4 h-4 text-brand-primary dark:text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <span>{{ __('global.filter_title') }}</span>
        </button>

        {{-- Mobile Sort --}}
        <form action="{{ request()->url() }}" method="GET" class="flex-1">
            <select name="sort" onchange="this.form.submit()"
                    class="w-full border border-slate-200/60 dark:border-slate-800/80 bg-white dark:bg-surface-dark text-slate-800 dark:text-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-brand-primary focus:border-brand-primary dark:focus:ring-accent dark:focus:border-accent focus:outline-none transition-all cursor-pointer">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('global.filter_sort_newest') }}</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('global.filter_sort_price_low') }}</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('global.filter_sort_price_high') }}</option>
            </select>
        </form>

        {{-- Mobile Filter Drawer --}}
        <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-50" id="mobile-filter-drawer" role="dialog" aria-modal="true" aria-label="{{ __('global.filter_title') }}">
            <div @click="mobileOpen = false" class="fixed inset-0 bg-black/70 backdrop-blur-md"></div>
            <div class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} w-72 sm:w-80 max-w-[85vw] bg-white/95 dark:bg-surface-dark/95 border-s border-slate-200/40 dark:border-slate-800/40 shadow-2xl z-10 flex flex-col backdrop-blur-xl"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 {{ app()->getLocale() === 'ar' ? '-translate-x-8' : 'translate-x-8' }}"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 {{ app()->getLocale() === 'ar' ? '-translate-x-8' : 'translate-x-8' }}">
                <div class="p-5 border-b border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between shrink-0">
                    <h3 class="font-extrabold text-sm text-slate-900 dark:text-white uppercase tracking-wider">{{ __('global.filter_title') }}</h3>
                    <button @click="mobileOpen = false" aria-label="Close filters" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ request()->url() }}" method="GET" class="p-5 space-y-4 overflow-y-auto flex-1" @submit="mobileOpen = false">
                    @if(request('sort'))
                        <a href="{{ request()->url() }}" class="block text-center text-xs text-red-500 hover:text-red-600 font-bold underline py-1 transition-colors">{{ __('global.filter_reset') }}</a>
                    @endif
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 btn-primary py-2.5 px-4 text-xs cursor-pointer">{{ __('global.filter_apply') }}</button>
                        <a href="{{ request()->url() }}" class="flex-1 block text-center border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 px-4 py-2.5 rounded-xl text-sm font-bold transition-all hover:bg-slate-50 dark:hover:bg-slate-900">{{ __('global.filter_reset') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Desktop Sidebar --}}
    <aside class="hidden md:block w-full md:w-60 flex-shrink-0" aria-label="Product Filters">
        <form action="{{ request()->url() }}" method="GET" id="filter-form"
              class="bg-white/60 dark:bg-surface-dark/60 rounded-2xl border border-slate-200/40 dark:border-slate-800/40 p-6 sticky top-24 backdrop-blur-md shadow-[0_8px_32px_0_rgba(31,38,135,0.03)] dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            <h3 class="font-extrabold text-xs text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-5 pb-3 border-b border-slate-200/40 dark:border-slate-800/40">{{ __('global.filter_title') }}</h3>

            @if(request('sort'))
                <a href="{{ request()->url() }}" class="mt-5 w-full block text-center text-xs text-red-500 hover:text-red-600 font-bold underline transition-colors">{{ __('global.filter_reset') }}</a>
            @endif
        </form>
    </aside>

    {{-- Product Grid Column --}}
    <div class="flex-1 min-w-0">
        {{-- Sort + Count Bar (desktop) --}}
        <div class="hidden md:flex justify-between items-center mb-6 px-6 py-4 bg-white/60 dark:bg-surface-dark/60 rounded-2xl border border-slate-200/40 dark:border-slate-800/40 backdrop-blur-md shadow-[0_8px_32px_0_rgba(31,38,135,0.03)] dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
            <p class="text-sm text-slate-600 dark:text-slate-400 font-semibold">
                <span class="font-extrabold text-brand-primary dark:text-accent text-lg">{{ $products->total() }}</span>
                <span class="mx-1">{{ __('global.product') }}</span>
            </p>
            <form action="{{ request()->url() }}" method="GET" id="sort-form-desktop" class="flex items-center gap-3">
                <label class="text-xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ __('global.filter_sort') }}</label>
                <select name="sort" onchange="document.getElementById('sort-form-desktop').submit()"
                        class="border border-slate-200/60 dark:border-slate-800/80 bg-white dark:bg-surface-dark text-slate-800 dark:text-slate-200 rounded-xl shadow-sm focus:border-brand-primary focus:ring-2 focus:ring-brand-primary dark:focus:border-accent dark:focus:ring-accent text-sm font-bold py-2 px-3.5 cursor-pointer hover:border-brand-primary dark:hover:border-accent transition-all duration-300">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('global.filter_sort_newest') }}</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('global.filter_sort_price_low') }}</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('global.filter_sort_price_high') }}</option>
                </select>
            </form>
        </div>

        @if($products->count() > 0)
            <div class="product-grid" x-data="{ stagger: 0 }">
                @foreach($products as $index => $product)
                    <div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, {{ $index * 80 }})"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                        <div x-show="!loaded">
                            @include('components.skeleton-product')
                        </div>
                        <div x-show="loaded" style="display: none;">
                            @include('shop.partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds ?? [], 'cartProductIds' => $cartProductIds ?? []])
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $products->links('pagination.tailwind') }}
            </div>
        @else
            {{-- Empty state --}}
            <div class="bg-white/60 dark:bg-surface-dark/60 rounded-2xl border border-slate-200/40 dark:border-slate-800/40 backdrop-blur-md shadow-sm p-16 text-center">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-5 text-slate-400 dark:text-slate-600">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-200 mb-2">{{ __('لا توجد منتجات') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs mx-auto">{{ __('عذراً، لم نتمكن من العثور على منتجات في هذا القسم حالياً.') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
