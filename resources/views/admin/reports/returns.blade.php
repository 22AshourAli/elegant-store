@extends('admin.layouts.app')

@section('page-title', __('global.admin_return_analysis'))

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-bold">&larr; {{ __('global.admin_back_to_reports') }}</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">{{ __('global.admin_by_product') }}</h3>
        @forelse($data['by_variant'] ?? [] as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->product_name ?? '#' . $item->product_variant_id }}</span>
                <span class="text-sm font-black text-rose-600">{{ $item->return_count }} {{ __('global.admin_return_count') }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">{{ __('global.admin_no_returns') }}</p>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">{{ __('global.admin_by_color') }}</h3>
        @forelse($data['by_color'] ?? [] as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->color ?? __('global.admin_na') }}</span>
                <span class="text-sm font-black text-rose-600">{{ $item->total }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">{{ __('global.admin_no_data') }}</p>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">{{ __('global.admin_by_size') }}</h3>
        @forelse($data['by_size'] ?? [] as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->size ?? __('global.admin_na') }}</span>
                <span class="text-sm font-black text-rose-600">{{ $item->total }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">{{ __('global.admin_no_data') }}</p>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">{{ __('global.admin_by_return_reason') }}</h3>
        @forelse($data['by_reason'] ?? [] as $item)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $item->reason ?? __('global.admin_not_specified') }}</span>
                <span class="text-sm font-black text-rose-600">{{ $item->total }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">{{ __('global.admin_no_data') }}</p>
        @endforelse
    </div>
</div>
@endsection