@extends('admin.layouts.app')

@section('page-title', __('global.admin_dead_stock'))

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-bold">&larr; {{ __('global.admin_back_to_reports') }}</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">{{ __('global.admin_dead_products') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <th class="text-right px-3 py-2">{{ __('global.admin_product') }}</th>
                        <th class="text-center px-3 py-2">{{ __('global.admin_stock') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse(($data['dead_stock'] ?? []) as $item)
                    <tr>
                        <td class="px-3 py-2 text-sm font-medium">{{ $item->product_name ?? '#' . $item->variant_id }}</td>
                        <td class="px-3 py-2 text-center font-black text-amber-600">{{ $item->total_stock }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center py-6 text-slate-400">{{ __('global.admin_no_dead_products') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
        <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">{{ __('global.admin_slow_movers') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <th class="text-right px-3 py-2">{{ __('global.admin_product') }}</th>
                        <th class="text-center px-3 py-2">{{ __('global.admin_sales') }}</th>
                        <th class="text-center px-3 py-2">{{ __('global.admin_stock') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse(($data['slow_movers'] ?? []) as $item)
                    <tr>
                        <td class="px-3 py-2 text-sm font-medium">{{ $item->product_name ?? '#' . $item->variant_id }}</td>
                        <td class="px-3 py-2 text-center">{{ $item->sales_count }}</td>
                        <td class="px-3 py-2 text-center font-black text-amber-600">{{ $item->total_stock }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center py-6 text-slate-400">{{ __('global.admin_no_slow_products') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection