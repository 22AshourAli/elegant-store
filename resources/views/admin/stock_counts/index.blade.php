@extends('admin.layouts.app')

@section('page-title', __('global.admin_stock_counts'))

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('global.admin_stock_counts') }}</h1>

    <div class="flex flex-wrap gap-3">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="branch_id" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
                <option value="">{{ __('global.admin_all_branches') }}</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
                <option value="">{{ __('global.admin_all_statuses') }}</option>
                @foreach(['draft','in_progress','completed','cancelled'] as $st)
                    <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ __('global.sc_status_' . $st) }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">{{ __('global.admin_filter') }}</button>
        </form>
        <a href="{{ route('admin.stock-counts.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('global.sc_new_count') }}
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    @if($counts->isEmpty())
        <div class="p-12 text-center text-gray-500">{{ __('global.sc_no_counts') }}</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600 text-sm font-semibold text-gray-500 dark:text-gray-300">
                        <th class="p-4">#</th>
                        <th class="p-4">{{ __('global.sc_reference') }}</th>
                        <th class="p-4">{{ __('global.admin_branch') }}</th>
                        <th class="p-4">{{ __('global.admin_status') }}</th>
                        <th class="p-4 hidden md:table-cell">{{ __('global.sc_items_counted') }}</th>
                        <th class="p-4 hidden md:table-cell">{{ __('global.admin_date') }}</th>
                        <th class="p-4 text-left">{{ __('global.admin_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($counts as $count)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 text-sm text-gray-900 dark:text-gray-200 transition-colors">
                        <td class="p-4 font-bold">{{ $count->id }}</td>
                        <td class="p-4 font-mono text-xs">{{ $count->reference_number }}</td>
                        <td class="p-4">{{ $count->branch->name ?? '—' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($count->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                @elseif($count->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                @elseif($count->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @endif">
                                {{ __('global.sc_status_' . $count->status) }}
                            </span>
                        </td>
                        <td class="p-4 hidden md:table-cell">
                            @if($count->status === 'completed')
                                <span class="text-green-600 dark:text-green-400 font-semibold">{{ __('global.sc_all_counted') }}</span>
                            @else
                                {{ $count->items()->whereNotNull('counted_stock')->count() }} / {{ $count->items()->count() }}
                            @endif
                        </td>
                        <td class="p-4 hidden md:table-cell">{{ $count->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-4 text-left">
                            <a href="{{ route('admin.stock-counts.show', $count) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold">
                                {{ __('global.admin_view_update') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($counts->hasPages())
            <div class="p-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                {{ $counts->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
