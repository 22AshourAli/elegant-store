@extends('delivery.layout')

@section('content')
<div class="space-y-4">
    {{-- Back link --}}
    <a href="{{ route('delivery.orders') }}" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-white transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        العودة للطلبات
    </a>

    {{-- Order header --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold">طلب #{{ $order->id }}</h1>
            <span class="px-3 py-1 rounded-full text-sm font-bold
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

        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-gray-500">العميل</span>
                <p class="font-medium">{{ $order->user->name ?? 'زبون' }}</p>
            </div>
            <div>
                <span class="text-gray-500">الهاتف</span>
                <p class="font-medium" dir="ltr">{{ $order->phone ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-gray-500">العنوان</span>
                <p class="font-medium">{{ $order->shipping_address }}</p>
            </div>
            <div>
                <span class="text-gray-500">المبلغ</span>
                <p class="font-bold text-indigo-400">{{ number_format($order->total, 2) }} ج.م</p>
            </div>
            <div>
                <span class="text-gray-500">الدفع</span>
                <p class="font-medium">{{ $order->payment_method === 'cash' ? 'كاش' : 'أونلاين' }}</p>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="font-bold mb-3">المنتجات</h2>
        <div class="space-y-3">
            @foreach($order->items as $item)
                <div class="flex items-center justify-between text-sm pb-3 border-b border-gray-800 last:border-0 last:pb-0">
                    <div>
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-xs text-gray-500">
                            @if($item->color) اللون: {{ $item->color }} @endif
                            @if($item->size) — المقاس: {{ $item->size }} @endif
                        </p>
                    </div>
                    <div class="text-left">
                        <p class="font-medium">{{ $item->quantity }} × {{ number_format($item->unit_price, 2) }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($item->total, 2) }} ج.م</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Status update --}}
    @if(!empty($statusLabels))
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h2 class="font-bold mb-3">تحديث الحالة</h2>
            <form method="POST" action="{{ route('delivery.orders.update-status', $order) }}" class="space-y-3"
                  x-data="{ selected: '' }">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($statusLabels as $value => $label)
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="selected === '{{ $value }}' ? 'border-indigo-500 bg-indigo-500/10' : 'border-gray-700 bg-gray-800 hover:border-gray-600'">
                            <input type="radio" name="status" value="{{ $value }}" x-model="selected" class="hidden">
                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                 :class="selected === '{{ $value }}' ? 'border-indigo-500' : 'border-gray-600'">
                                <div x-show="selected === '{{ $value }}'" class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                            </div>
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <div>
                    <input type="text" name="note" placeholder="ملاحظة (اختياري)"
                           class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!selected">
                    تحديث الحالة
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
