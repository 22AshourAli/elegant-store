@extends('admin.layouts.app')
@section('page-title', __('global.exchange_requests'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm text-right">
        <thead class="bg-gray-50 dark:bg-gray-700">
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
                <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer even:bg-gray-50/50 dark:even:bg-gray-700/20" onclick="window.location='{{ route('admin.exchanges.show', $exchange) }}'">
                    <td class="p-3">{{ $exchange->id }}</td>
                    <td class="p-3">{{ $exchange->user->name }}</td>
                    <td class="p-3">#{{ $exchange->order_id }}</td>
                    <td class="p-3 max-w-xs truncate hidden md:table-cell">{{ Str::limit($exchange->reason, 60) }}</td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold
                            @if($exchange->status === 'pending') bg-amber-100 text-amber-700
                            @elseif($exchange->status === 'approved') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ $exchange->status }}
                        </span>
                    </td>
                    <td class="p-3 text-xs text-gray-500 hidden md:table-cell">{{ $exchange->created_at->format('Y-m-d') }}</td>
                    <td class="p-3 text-right"><a href="{{ route('admin.exchanges.show', $exchange) }}" class="text-indigo-600 hover:underline text-xs font-bold" onclick="event.stopPropagation()">{{ __('global.view') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-6 text-center text-gray-400">{{ __('global.no_exchange_requests') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $exchanges->onEachSide(1)->links('vendor.pagination.admin') }}</div>
@endsection
