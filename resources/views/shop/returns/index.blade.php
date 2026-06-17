@extends('layouts.store')

@section('content')
<div class="bg-gray-50 dark:bg-gray-800/50 py-6 border-b border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold">{{ __('return.my_returns') }}</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    @if($returns->count() > 0)
        <div class="space-y-4">
            @foreach($returns as $return)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <span class="font-bold">{{ __('return.request_for') }} #{{ $return->order_id }}</span>
                            <span class="text-sm text-gray-500 block">{{ $return->created_at->format('Y-m-d') }}</span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            @if($return->status === 'pending') bg-amber-100 text-amber-700
                            @elseif($return->status === 'approved') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ __('return.status_' . $return->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ Str::limit($return->reason, 200) }}</p>
                    @if($return->admin_note)
                        <p class="text-xs text-gray-400 mt-2">{{ __('return.admin_response') }}: {{ $return->admin_note }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        @if($returns->hasPages())
            <div class="mt-6">{{ $returns->links() }}</div>
        @endif
    @else
        <div class="text-center py-12">
            <p class="text-gray-500">{{ __('return.no_returns') }}</p>
        </div>
    @endif
</div>
@endsection
