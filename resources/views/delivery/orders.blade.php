@extends('delivery.layout')

@section('title', 'الطلبات')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-lg font-bold text-gray-900">الطلبات المتاحة للتوصيل</h1>
    <div class="flex gap-2">
        <a href="{{ route('delivery.orders') }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">الكل</a>
        <a href="{{ route('delivery.orders', ['status' => 'shipped']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ request('status') === 'shipped' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}>تم الشحن</a>
        <a href="{{ route('delivery.orders', ['status' => 'out_for_delivery']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ request('status') === 'out_for_delivery' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}>خرج للتوصيل</a>
    </div>
</div>

@if($orders->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <p class="text-gray-400 text-sm">لا توجد طلبات حالياً</p>
    </div>
@else
    <div class="space-y-3">
        @foreach($orders as $order)
            <a href="{{ route('delivery.order.show', $order) }}"
               class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-bold text-gray-900">#{{ $order->id }}</span>
                            @php
                                $statusColors = [
                                    'shipped' => 'bg-blue-100 text-blue-700',
                                    'out_for_delivery' => 'bg-amber-100 text-amber-700',
                                    'delivered' => 'bg-green-100 text-green-700',
                                    'returned' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ \App\Services\OrderStateMachine::statusLabel($order->status, app()->getLocale()) }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 mb-1">{{ $order->user->name ?? 'عميل' }} - {{ $order->phone }}</div>
                        <div class="text-xs text-gray-400">
                            {{ $order->address_street ?? '' }}
                            @if($order->governorate) {{ $order->governorate->name }} @endif
                            @if($order->city) - {{ $order->city->name }} @endif
                        </div>
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-bold text-gray-900">{{ number_format($order->total, 0) }} ج.م</div>
                        <div class="text-[10px] text-gray-400 mt-1">{{ $order->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @if($order->delivery_notes)
                    <div class="mt-2 px-3 py-1.5 bg-amber-50 rounded-lg text-xs text-amber-700">
                        {{ $order->delivery_notes }}
                    </div>
                @endif
            </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endif
@endsection
