@extends('admin.layouts.app')

@section('title', __('global.admin_reviews'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <a href="{{ route('admin.reviews.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition mb-4">
        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        {{ __('global.admin_back_to_list') }}
    </a>

    <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
        {{-- Main Review Card --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Review Detail --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">{{ __('global.review') }}</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ __('submission_date') }}: {{ $review->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="flex-shrink-0">
                        @if($review->status === 'pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ __('global.pending') }}</span>
                        @elseif($review->status === 'approved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ __('global.approved') }}</span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ __('global.rejected') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Rating --}}
                <div class="mb-5">
                    <label class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 block">{{ __('global.rating') }}</label>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-6 h-6 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 mr-2">{{ $review->rating }}/5</span>
                    </div>
                </div>

                {{-- Comment --}}
                <div class="mb-4">
                    <label class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 block">{{ __('global.comment') }}</label>
                    <div class="bg-slate-50 dark:bg-gray-700/50 rounded-xl p-4 border border-slate-200 dark:border-gray-700">
                        @if($review->comment)
                            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap" dir="auto">{{ $review->comment }}</p>
                        @else
                            <p class="text-sm text-slate-400 dark:text-slate-500 italic">—</p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                @if($review->status === 'pending')
                <div class="flex flex-col sm:flex-row gap-2 pt-4 border-t border-slate-200 dark:border-gray-700">
                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-extrabold py-2.5 px-4 rounded-xl transition active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ __('global.approve') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white text-sm font-extrabold py-2.5 px-4 rounded-xl transition active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ __('global.reject') }}
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            {{-- Customer Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
                <h3 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">{{ __('global.customer') }}</h3>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-950/50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate" dir="auto">{{ $review->user->name }}</p>
                        @if($review->user->email)
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $review->user->email }}</p>
                        @endif
                    </div>
                </div>
                @if($review->user->phone)
                    <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span dir="ltr">{{ $review->user->phone }}</span>
                    </div>
                @endif
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2">{{ __('registered') }}: {{ $review->user->created_at->format('Y-m-d') }}</p>
            </div>

            {{-- Product Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
                <h3 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">{{ __('global.product') }}</h3>
                <a href="{{ route('shop.product', $review->product->slug) }}" target="_blank" class="group flex items-start gap-3">
                    <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-gray-700 flex-shrink-0 overflow-hidden">
                        <img src="{{ $review->product->firstImageUrl() }}" alt="" class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400 group-hover:underline truncate" dir="auto">{{ $review->product->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('global.price') }}: {{ (int)$review->product->current_price }} {{ __('global.currency') }}</p>
                    </div>
                </a>
            </div>

            {{-- Order Info --}}
            @if($review->order)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
                <h3 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">{{ __('global.order') }}</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('global.order_number') }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">#{{ $review->order->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('global.order_date') }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $review->order->created_at->format('Y-m-d') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('global.status') }}</span>
                        <span class="font-bold">{{ __("global.orders.status_{$review->order->status}") }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
