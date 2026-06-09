@extends('layouts.store')

@section('content')
<div class="container py-8">
    <div class="mb-6">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-primary dark:hover:text-accent transition font-bold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
            {{ __('global.back_to_home') }}
        </a>
    </div>

    <div class="text-center mb-10">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">
            {{ __('global.search_results_for') }} "{{ $query }}"
        </h1>
        <p class="text-slate-500 text-sm">
            {{ $products->total() }} {{ __('global.product') }} {{ __('global.found') }}
        </p>
    </div>

    <div id="product-grid">
        @include('shop.partials.product-grid', ['products' => $products, 'wishlistIds' => $wishlistIds ?? [], 'cartProductIds' => $cartProductIds ?? []])
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-brand-primary dark:text-accent hover:text-brand-hover font-bold transition text-sm">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
            {{ __('global.back_to_home') }}
        </a>
    </div>
</div>
@endsection