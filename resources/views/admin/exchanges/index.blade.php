@extends('admin.layouts.app')
@section('page-title', __('global.exchange_requests'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 overflow-hidden">
    <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center gap-3">
        <h3 class="font-extrabold text-lg">{{ __('global.exchange_requests') }} <span class="text-slate-400 text-sm font-bold">({{ $exchanges->total() }})</span></h3>
        <div class="flex gap-2 ms-auto">
            <a href="{{ route('admin.exchanges.index') }}?type=online" class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all {{ request('type') === 'online' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">{{ __('global.admin_online') }}</a>
            <a href="{{ route('admin.exchanges.index') }}?type=offline" class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all {{ request('type') === 'offline' ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">{{ __('global.admin_offline') }}</a>
            @if(request('type'))
                <a href="{{ route('admin.exchanges.index') }}" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-all">{{ __('global.admin_cancel') }}</a>
            @endif
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-right">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">{{ __('global.customer') }}</th>
                    <th class="p-3">{{ __('global.order') }}</th>
                    <th class="p-3 hidden md:table-cell">{{ __('global.reason') }}</th>
                    <th class="p-3">{{ __('global.status') }}</th>
                    <th class="p-3 hidden md:table-cell">{{ __('global.date') }}</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($exchanges as $exchange)
                    <tr class="border-t border-slate-100 dark:border-slate-700 hover:bg-slate-50/50 dark:hover:bg-slate-700/20 cursor-pointer even:bg-gray-50/50 dark:even:bg-gray-700/20" onclick="window.location='{{ route('admin.exchanges.show', $exchange) }}'">
                        <td class="p-3 text-xs">{{ $exchange->id }}</td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold">{{ $exchange->user->name }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $exchange->order?->order_type === 'offline' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $exchange->order?->order_type === 'offline' ? __('global.admin_offline') : __('global.admin_online') }}
                                </span>
                            </div>
                        </td>
                        <td class="p-3 text-xs">#{{ $exchange->order_id }}</td>
                        <td class="p-3 max-w-xs truncate hidden md:table-cell text-xs">{{ Str::limit($exchange->reason, 60) }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap
                                @if($exchange->status === 'pending') bg-amber-100 text-amber-700
                                @elseif($exchange->status === 'approved' || $exchange->status === 'completed') bg-green-100 text-green-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $exchange->status }}
                            </span>
                        </td>
                        <td class="p-3 text-[10px] text-gray-500 hidden md:table-cell">{{ $exchange->created_at->format('Y-m-d') }}</td>
                        <td class="p-3 text-center"><a href="{{ route('admin.exchanges.show', $exchange) }}" class="text-indigo-600 hover:underline text-[10px] font-bold" onclick="event.stopPropagation()">{{ __('global.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-10 text-center text-slate-400 text-sm font-bold">{{ __('global.no_exchange_requests') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($exchanges->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $exchanges->onEachSide(1)->links('vendor.pagination.admin') }}
        </div>
    @endif
</div>
@endsection
