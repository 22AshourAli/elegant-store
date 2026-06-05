@extends('layouts.store')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-2xl">
    <h1 class="text-3xl font-extrabold mb-8 text-gray-900 dark:text-white">الإشعارات</h1>

    <div class="space-y-4">
        @if($notifications->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <h3 class="text-lg font-bold mb-1">لا توجد إشعارات</h3>
                <p class="text-gray-500">صندوق الإشعارات الخاص بك فارغ حالياً.</p>
            </div>
        @else
            @foreach($notifications as $notification)
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex items-start gap-4 transition-all hover:shadow-md {{ $notification->read_at ? 'opacity-60' : 'border-r-4 border-r-indigo-500' }}">
                    <div class="p-2 rounded-full bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white leading-relaxed">
                            {{ $notification->data['message'] ?? '' }}
                        </p>
                        <span class="text-xs text-gray-400 mt-2 block">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    @if(isset($notification->data['order_id']))
                        @php
                            $user = auth()->user();
                            $orderRoute = ($user->isSuperAdmin() || $user->isManager()) 
                                ? route('admin.orders.show', $notification->data['order_id']) 
                                : route('orders.show', $notification->data['order_id']);
                        @endphp
                        <a href="{{ $orderRoute }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">عرض الطلب &larr;</a>
                    @endif
                </div>
            @endforeach

            @if($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
