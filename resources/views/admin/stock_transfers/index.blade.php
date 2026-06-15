@extends('admin.layouts.app')
@section('page-title', __('global.admin_stock_transfers'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_stock_transfers') }}</h2>
    <a href="{{ route('admin.stock-transfers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_transfer') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_ref_no') }}</th>
                <th class="p-3 text-right hidden md:table-cell">{{ __('global.admin_from_branch') }}</th>
                <th class="p-3 text-right hidden md:table-cell">{{ __('global.admin_to_branch') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_date') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $transfer)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer even:bg-gray-50/50 dark:even:bg-gray-700/20" onclick="window.location='{{ route('admin.stock-transfers.show', $transfer) }}'">
                <td class="p-3 font-mono text-xs">{{ $transfer->reference_number }}</td>
                <td class="p-3 hidden md:table-cell">{{ $transfer->fromBranch->name }}</td>
                <td class="p-3 hidden md:table-cell">{{ $transfer->toBranch->name }}</td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs @switch($transfer->status)
                        @case('pending') bg-yellow-100 text-yellow-800 @break
                        @case('completed') bg-green-100 text-green-800 @break
                        @case('cancelled') bg-red-100 text-red-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch">
                        {{ __('global.st_status_' . $transfer->status) }}
                    </span>
                </td>
                <td class="p-3 text-xs">{{ $transfer->created_at->format('Y-m-d') }}</td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.stock-transfers.show', $transfer) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1" onclick="event.stopPropagation()">{{ __('global.admin_view') }}</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-4 text-center text-gray-500">{{ __('global.admin_no_transfers') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if(method_exists($transfers, 'links'))
<div class="mt-4">{{ $transfers->onEachSide(1)->links('vendor.pagination.admin') }}</div>
@endif
@endsection
