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
                    <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('الرئيسية') }}</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1 {{ app()->getLocale() === 'ar' ? 'transform rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('التصنيفات') }}</a>
                    </div>
                </li>
                @if($category->parent)
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1 {{ app()->getLocale() === 'ar' ? 'transform rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('shop.category', $category->parent->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $category->parent->name }}</a>
                    </div>
                </li>
                @endif
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1 {{ app()->getLocale() === 'ar' ? 'transform rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ $category->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">{{ $category->name }}</h1>
        @if($category->children->count() > 0)
            <div class="flex flex-wrap gap-2 mt-6">
                @foreach($category->children as $child)
                    <a href="{{ route('shop.category', $child->slug) }}" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full text-sm font-medium hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="reveal container mx-auto px-4 py-12 flex flex-col md:flex-row gap-8">
    <!-- Sidebar Filters -->
    <aside class="w-full md:w-64 flex-shrink-0">
        <form action="{{ request()->url() }}" method="GET" id="filter-form" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6 sticky top-24">
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            <h3 class="font-bold text-lg mb-4 pb-2 border-b dark:border-gray-700">{{ __('تصفية النتائج') }}</h3>
            
            <div class="mb-4">
                <label class="flex items-center text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-indigo-600 transition">
                    <input type="checkbox" name="in_stock" value="1" onchange="document.getElementById('filter-form').submit()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-5 h-5" {{ request('in_stock') ? 'checked' : '' }}>
                    <span class="ml-3 rtl:mr-3 rtl:ml-0 font-medium">{{ __('متوفر في المخزون فقط') }}</span>
                </label>
            </div>
            @if(request()->hasAny(['in_stock', 'sort']))
                <a href="{{ request()->url() }}" class="mt-4 w-full block text-center text-xs text-red-500 hover:text-red-700 underline">{{ __('إلغاء التصفية') }}</a>
            @endif
        </form>
    </aside>

    <!-- Product Grid -->
    <div class="flex-1">
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600 dark:text-gray-400 font-medium">
                {{ __('إظهار') }} <span class="font-bold text-indigo-600">{{ $products->total() }}</span> {{ __('منتج') }}
            </p>
            <form action="{{ request()->url() }}" method="GET" id="sort-form">
                @if(request('in_stock'))
                    <input type="hidden" name="in_stock" value="{{ request('in_stock') }}">
                @endif
                <select name="sort" onchange="document.getElementById('sort-form').submit()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium py-2 px-4 cursor-pointer hover:border-indigo-400 transition">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('الأحدث') }}</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('السعر: من الأقل للأعلى') }}</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('السعر: من الأعلى للأقل') }}</option>
                </select>
            </form>
        </div>

        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                    <div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 800)">
                        <div x-show="!loaded">
                            @include('components.skeleton-product')
                        </div>
                        <div x-show="loaded" x-transition.opacity.duration.500 style="display: none;">
                            @include('shop.partials.product-card', ['product' => $product])
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">{{ __('لا توجد منتجات') }}</h3>
                <p class="text-gray-500">{{ __('عذراً، لم نتمكن من العثور على منتجات في هذا القسم حالياً.') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
