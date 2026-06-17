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
                    <a href="{{ route('home') }}" class="hover:text-brand-primary dark:hover:text-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded">{{ __('global.home') }}</a>
                </li>
                <li class="inline-flex items-center gap-1.5">
                    <svg class="w-3 h-3 rtl:rotate-180 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="#" class="hover:text-brand-primary dark:hover:text-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded">{{ __('global.categories') }}</a>
                </li>
                @if($category->parent)
                <li class="inline-flex items-center gap-1.5">
                    <svg class="w-3 h-3 rtl:rotate-180 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('shop.category', $category->parent->slug) }}" class="hover:text-brand-primary dark:hover:text-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded" dir="auto">{{ $category->parent->name }}</a>
                </li>
                @endif
                <li class="inline-flex items-center gap-1.5" aria-current="page">
                    <svg class="w-3 h-3 rtl:rotate-180 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="text-slate-700 dark:text-slate-300 font-bold" dir="auto">{{ $category->name }}</span>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight" dir="auto">{{ $category->name }}</h1>

        {{-- Subcategory chips --}}
        @if($category->children->count() > 0)
            <div class="flex flex-wrap gap-2.5 mt-6 animate-fadeInUp" role="list" aria-label="Subcategories">
                @foreach($category->children as $child)
                    <a href="{{ route('shop.category', $child->slug) }}"
                       role="listitem"
                       class="px-5 py-2 bg-white/70 dark:bg-surface-dark/50 border border-slate-200/60 dark:border-slate-800/80 rounded-full text-xs font-extrabold text-slate-600 dark:text-slate-300 hover:border-brand-primary dark:hover:border-accent hover:text-brand-primary dark:hover:text-white hover:-translate-y-0.5 shadow-sm hover:shadow-[0_4px_12px_rgba(79,70,229,0.1)] transition-all duration-300 backdrop-blur-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary" dir="auto">
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="reveal container py-10 flex flex-col md:flex-row gap-8">

    {{-- Sort + Count Bar (mobile) --}}
    <div class="md:hidden mb-6">
        <form action="{{ request()->url() }}" method="GET" class="flex items-center gap-3">
            <select name="sort" onchange="this.form.submit()"
                    class="w-full border border-slate-200/60 dark:border-slate-800/80 bg-white dark:bg-surface-dark text-slate-800 dark:text-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-brand-primary focus:border-brand-primary dark:focus:ring-accent dark:focus:border-accent focus:outline-none transition-all cursor-pointer">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('global.filter_sort_newest') }}</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('global.filter_sort_price_low') }}</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('global.filter_sort_price_high') }}</option>
            </select>
        </form>
    </div>

    {{-- Sort + Count Bar (desktop) --}}
    <div class="hidden md:flex justify-between items-center mb-6 px-6 py-4 bg-white/60 dark:bg-surface-dark/60 rounded-2xl border border-slate-200/40 dark:border-slate-800/40 backdrop-blur-md shadow-[0_8px_32px_0_rgba(31,38,135,0.03)] dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]">
            <p class="text-sm text-slate-600 dark:text-slate-400 font-semibold">
                <span class="font-extrabold text-brand-primary dark:text-accent text-lg">{{ $products->count() }}</span>
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
                {{ $products->links() }}
            </div>
        @else
            {{-- Empty state --}}
            <div class="bg-white/60 dark:bg-surface-dark/60 rounded-2xl border border-slate-200/40 dark:border-slate-800/40 backdrop-blur-md shadow-sm p-16 text-center">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-5 text-slate-400 dark:text-slate-600">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-200 mb-2">{{ __('global.filter_no_results') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs mx-auto">{{ __('global.filter_no_results_desc') }}</p>
            </div>
        @endif
</div>
@endsection
