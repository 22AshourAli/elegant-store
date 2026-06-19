@extends('admin.layouts.app')

@section('title', __('global.admin_backups'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('global.admin_backups') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('global.admin_backups_description') }}</p>
        </div>
        <form action="{{ route('admin.backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-extrabold px-4 py-2.5 rounded-xl transition active:scale-[0.98] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ __('global.backup_create_new') }}
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-sm font-bold text-red-700 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-900/50">
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.backup_file') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.backup_size') }}</th>
                        <th class="p-3 text-right text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.backup_date') }}</th>
                        <th class="p-3 text-center text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('global.backup_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                    <tr class="border-b border-slate-100 dark:border-gray-700/50 hover:bg-slate-50/50 dark:hover:bg-gray-700/20">
                        <td class="p-3 text-right">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 flex-shrink-0 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0014.586 3H7a2 2 0 00-2 2v2m0 0a2 2 0 012-2h4.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0118 11.414V15a2 2 0 01-2 2h-2m-7-4a2 2 0 00-2 2v4a2 2 0 002 2h10a2 2 0 002-2v-4a2 2 0 00-2-2H9z"/></svg>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate max-w-[200px]" dir="ltr">{{ $file['name'] }}</span>
                            </div>
                        </td>
                        <td class="p-3 text-right">
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ $file['size_formatted'] }}</span>
                        </td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <span class="text-xs text-slate-600 dark:text-slate-400">{{ date('Y-m-d H:i', $file['date']) }}</span>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.backups.download', $file['name']) }}" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 px-2 py-1 rounded hover:bg-indigo-50 dark:hover:bg-indigo-950/20 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    {{ __('global.backup_download') }}
                                </a>
                                <form action="{{ route('admin.backups.destroy', $file['name']) }}" method="POST" onsubmit="return confirm('{{ __('global.backup_delete_confirm') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 text-[10px] font-bold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-950/20 transition cursor-pointer">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        {{ __('global.backup_delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-sm text-slate-400 dark:text-slate-500">{{ __('global.backup_no_files') }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ __('global.backup_create_first') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
