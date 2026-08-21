@extends('delivery.layout')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">طلباتي</h1>
        <span class="text-sm text-gray-400">{{ $orders->total() }} طلب</span>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-16 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="font-medium">لا توجد طبات حالياً</p>
        </div>
    @endif

    @foreach($orders as $order)
        <a href="{{ route('delivery.orders.show', $order) }}"
           class="block bg-gray-900 border border-gray-800 rounded-xl p-4 hover:border-indigo-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-white">#{{ $order->id }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                        @if($order->status === 'shipped') bg-blue-500/20 text-blue-400
                        @elseif($order->status === 'out_for_delivery') bg-amber-500/20 text-amber-400
                        @elseif($order->status === 'delivered') bg-emerald-500/20 text-emerald-400
                        @else bg-gray-500/20 text-gray-400
                        @endif">
                        @if($order->status === 'shipped') تم الشحن
                        @elseif($order->status === 'out_for_delivery') خرج للتوصيل
                        @elseif($order->status === 'delivered') تم التوصيل
                        @else {{ $order->status }}
                        @endif
                    </span>
                </div>
                <span class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="text-sm text-gray-400 space-y-1">
                <p>{{ $order->user->name ?? 'زبون' }} — {{ $order->phone ?? '' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $order->shipping_address }}</p>
                <p class="font-bold text-white">{{ number_format($order->total, 2) }} ج.م</p>
            </div>
        </a>
    @endforeach

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
