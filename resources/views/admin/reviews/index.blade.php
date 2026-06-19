@extends('admin.layouts.app')

@section('title', __('global.admin_reviews'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('global.admin_reviews') }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50">
                        <th class="p-3 text-right font-extrabold text-xs text-slate-500 dark:text-slate-400">{{ __('global.product') }}</th>
                        <th class="p-3 text-right font-extrabold text-xs text-slate-500 dark:text-slate-400">{{ __('global.customer') }}</th>
                        <th class="p-3 text-center font-extrabold text-xs text-slate-500 dark:text-slate-400">{{ __('global.rating') }}</th>
                        <th class="p-3 text-right font-extrabold text-xs text-slate-500 dark:text-slate-400">{{ __('global.comment') }}</th>
                        <th class="p-3 text-center font-extrabold text-xs text-slate-500 dark:text-slate-400">{{ __('global.status') }}</th>
                        <th class="p-3 text-center font-extrabold text-xs text-slate-500 dark:text-slate-400">{{ __('global.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr class="border-b border-slate-100 dark:border-gray-700/50 hover:bg-slate-50/50 dark:hover:bg-gray-700/20 cursor-pointer" onclick="window.location='{{ route('admin.reviews.show', $review) }}'">
                        <td class="p-3 text-right">
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $review->product->name }}</span>
                        </td>
                        <td class="p-3 text-right">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $review->user->name }}</span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="inline-flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </span>
                        </td>
                        <td class="p-3 text-right max-w-[200px]">
                            <span class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $review->comment ?: '—' }}</span>
                        </td>
                        <td class="p-3 text-center">
                            @if($review->status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ __('global.pending') }}</span>
                            @elseif($review->status === 'approved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ __('global.approved') }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ __('global.rejected') }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-center" onclick="event.stopPropagation()">
                            @if($review->status === 'pending')
                            <div class="flex items-center justify-center gap-1">
                                <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-bold text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 px-2 py-1 rounded hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition cursor-pointer">{{ __('global.approve') }}</button>
                                </form>
                                <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-bold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-950/20 transition cursor-pointer">{{ __('global.reject') }}</button>
                                </form>
                            </div>
                            @else
                                <span class="text-[10px] text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                <p class="text-sm font-bold text-slate-400 dark:text-slate-500">{{ __('global.no_reviews_yet') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
