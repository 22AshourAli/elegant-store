@extends('layouts.store')

@section('seo')
    {!! SEO::generate() !!}
@endsection

@section('content')
<div class="bg-gray-50 dark:bg-gray-800/50 py-8 border-b border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 space-x-reverse md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('global.home') }}</a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1 {{ app()->getLocale() === 'ar' ? 'transform rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ __('global.wishlist') }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">{{ __('global.wishlist') }}</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-12">
    @if($products->count() > 0)
        <div class="product-grid in">
            @foreach($products as $product)
                @include('shop.partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds, 'cartProductIds' => $cartProductIds ?? [], 'hideOnRemove' => true])
            @endforeach
        </div>

        <div class="mt-10">
            {{ $products->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold mb-2">{{ __('global.empty_wishlist') }}</h3>
            <p class="text-gray-500 mb-6">{{ __('global.empty_wishlist_desc') }}</p>
            <a href="{{ route('home') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg transition">{{ __('global.shop_now') }}</a>
        </div>
    @endif
</div>
@endsection
