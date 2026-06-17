@extends('layouts.store')

@section('content')
<div class="bg-gray-50 dark:bg-gray-800/50 py-6 border-b border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold">{{ __('return.my_exchanges') }}</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    @if($exchanges->count() > 0)
        <div class="space-y-4">
            @foreach($exchanges as $exchange)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <span class="font-bold">{{ __('return.exchange_request_for') }} #{{ $exchange->order_id }}</span>
                            <span class="text-sm text-gray-500 block">{{ $exchange->created_at->format('Y-m-d') }}</span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if($exchange->status === 'pending') bg-amber-100 text-amber-700
                            @elseif($exchange->status === 'approved') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ __('return.status_' . $exchange->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ Str::limit($exchange->reason, 200) }}</p>
                    @if($exchange->admin_note)
                        <p class="text-xs text-gray-400 mt-2">{{ __('return.admin_response') }}: {{ $exchange->admin_note }}</p>
                    @endif
                    @if($exchange->items)
                        <details class="mt-3">
                            <summary class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold cursor-pointer">{{ __('return.exchange_items') }}</summary>
                            <ul class="mt-2 space-y-1 text-xs text-gray-500">
                                @foreach($exchange->items as $item)
                                    <li>• {{ __('return.item_id') }} #{{ $item['order_item_id'] }} → {{ __('return.new_variant') }} #{{ $item['new_variant_id'] }}</li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endforeach
        </div>
        @if($exchanges->hasPages())
            <div class="mt-6">{{ $exchanges->links() }}</div>
        @endif
    @else
        <div class="text-center py-12">
            <p class="text-gray-500">{{ __('return.no_exchanges') }}</p>
        </div>
    @endif
</div>
@endsection
