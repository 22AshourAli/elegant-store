@extends('admin.layouts.app')

@section('page-title', __('global.admin_notifications'))

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('global.admin_notifications') }}</h1>
</div>

@if(session('success'))
    <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 p-4 rounded-xl mb-4 text-sm font-medium">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    @if($notifications->isEmpty())
        <div class="p-12 text-center text-gray-500 dark:text-gray-400">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </div>
            <p class="text-lg font-semibold mb-1">{{ __('global.admin_no_notifications') }}</p>
        </div>
    @else
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($notifications as $notification)
                @php
                    $notifData = $notification->data;
                    $isUnread = is_null($notification->read_at);
                @endphp
                    @php
                        $notifType = $notifData['type'] ?? null;
                        $notifUrl = null;
                        if (in_array($notifType, ['exchange', 'exchange_approved', 'exchange_submitted'])) {
                            $notifUrl = isset($notifData['exchange_id'])
                                ? route('admin.exchanges.show', $notifData['exchange_id'])
                                : (isset($notifData['return_request_id'])
                                    ? route('admin.exchanges.show', $notifData['return_request_id'])
                                    : null);
                        } elseif ($notifType === 'return') {
                            $notifUrl = isset($notifData['return_request_id'])
                                ? route('admin.returns.show', $notifData['return_request_id'])
                                : null;
                        } elseif (isset($notifData['order_id'])) {
                            $notifUrl = route('admin.orders.show', $notifData['order_id']);
                        }
                    @endphp
                    <div class="flex items-start gap-4 p-4 transition {{ $isUnread ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-r-4 border-r-indigo-500' : 'opacity-70' }}">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                        {{ in_array($notifType, ['exchange', 'exchange_approved', 'exchange_submitted']) ? 'bg-green-100 dark:bg-green-900/30 text-green-600' : ($notifType === 'return' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' : (isset($notifData['order_id']) ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-500')) }}">
                        @if(in_array($notifType, ['exchange', 'exchange_approved', 'exchange_submitted']))
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        @elseif($notifType === 'return')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3"/></svg>
                        @elseif(isset($notifData['order_id']))
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $notifData['title'] ?? $notifData['message'] ?? '' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($notifUrl)
                            <a href="{{ $notifUrl }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('global.admin_view_update') }}</a>
                        @endif
                        @if($isUnread)
                            <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" class="inline">
                                @csrf
                                <button class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline">{{ __('global.admin_mark_read') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($notifications->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $notifications->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
