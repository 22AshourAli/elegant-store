@extends('admin.layouts.app')

@section('title', __('global.admin_activity_logs'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('global.admin_activity_logs') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('global.admin_activity_logs_description') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.activity-logs.index') }}" class="text-[11px] font-bold px-3 py-1.5 rounded-lg transition {{ !request('module') && !request('action') ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400' : 'bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-gray-700' }}">
            {{ __('global.all') }}
        </a>
        @foreach($modules as $module)
        <a href="{{ route('admin.activity-logs.index', ['module' => $module, 'action' => request('action')]) }}" class="text-[11px] font-bold px-3 py-1.5 rounded-lg transition {{ request('module') === $module ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400' : 'bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-gray-700' }}">
            {{ __("global.activity_module_{$module}") }}
        </a>
        @endforeach
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-900/50">
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.activity_admin') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.activity_module') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.activity_action') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.activity_description') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.activity_ip') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.activity_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="border-b border-slate-100 dark:border-gray-700/50 hover:bg-slate-50/50 dark:hover:bg-gray-700/20">
                        <td class="p-3 text-right">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950/40 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $log->user?->name ?? __('global.system') }}</span>
                            </div>
                        </td>
                        <td class="p-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-slate-300">
                                {{ __("global.activity_module_{$log->module}") }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <span class="inline-flex items-center gap-1 text-xs font-bold
                                @if($log->action === 'created') text-emerald-600 dark:text-emerald-400
                                @elseif($log->action === 'deleted') text-red-600 dark:text-red-400
                                @elseif(in_array($log->action, ['approved', 'rejected'])) text-amber-600 dark:text-amber-400
                                @else text-indigo-600 dark:text-indigo-400
                                @endif">
                                @if($log->action === 'created')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                @elseif($log->action === 'deleted')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                @elseif($log->action === 'approved')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @elseif($log->action === 'rejected')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                @endif
                                {{ __("global.activity_action_{$log->action}") }}
                            </span>
                        </td>
                        <td class="p-3 text-right max-w-[250px]">
                            <span class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $log->description ?: '—' }}</span>
                        </td>
                        <td class="p-3 text-right">
                            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500" dir="ltr">{{ $log->ip_address ?: '—' }}</span>
                        </td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('global.activity_no_logs') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
