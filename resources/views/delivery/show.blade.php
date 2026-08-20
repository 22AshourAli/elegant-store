@extends('delivery.layout')

@section('title', "طلب #{$order->id}")

@section('content')
<div class="mb-4">
    <a href="{{ route('delivery.orders') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">&larr; العودة للطلبات</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900">طلب #{{ $order->id }}</h2>
                @php
                    $statusColors = [
                        'shipped' => 'bg-blue-100 text-blue-700',
                        'out_for_delivery' => 'bg-amber-100 text-amber-700',
                        'delivered' => 'bg-green-100 text-green-700',
                        'returned' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ \App\Services\OrderStateMachine::statusLabel($order->status, app()->getLocale()) }}
                </span>
            </div>

            <div class="space-y-2">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div>
                            <span class="text-sm font-medium text-gray-800">{{ $item->product_name }}</span>
                            @if($item->color || $item->size)
                                <span class="text-xs text-gray-400 mr-2">
                                    @if($item->color) {{ $item->color }} @endif
                                    @if($item->size) / {{ $item->size }} @endif
                                </span>
                            @endif
                            <span class="text-xs text-gray-400 mr-2">&times;{{ $item->quantity }}</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($item->total, 0) }} ج.م</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between">
                <span class="text-sm font-bold text-gray-900">الإجمالي</span>
                <span class="text-sm font-bold text-indigo-600">{{ number_format($order->total, 0) }} ج.م</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">تفاصيل العنوان</h3>
            <div class="text-sm text-gray-600 space-y-1">
                <div><strong>الاسم:</strong> {{ $order->user->name ?? 'عميل' }}</div>
                <div><strong>التليفون:</strong> {{ $order->phone }}</div>
                @if($order->address_street) <div><strong>الشارع:</strong> {{ $order->address_street }}</div> @endif
                @if($order->address_building) <div><strong>المبنى:</strong> {{ $order->address_building }}</div> @endif
                @if($order->address_floor) <div><strong>الدور:</strong> {{ $order->address_floor }}</div> @endif
                @if($order->address_apartment) <div><strong>الشقة:</strong> {{ $order->address_apartment }}</div> @endif
                @if($order->address_landmark) <div><strong>علامة مميزة:</strong> {{ $order->address_landmark }}</div> @endif
                @if($order->governorate) <div><strong>المحافظة:</strong> {{ $order->governorate->name }}</div> @endif
                @if($order->city) <div><strong>المدينة:</strong> {{ $order->city->name }}</div> @endif
                @if($order->notes) <div class="mt-2 p-2 bg-gray-50 rounded-lg"><strong>ملاحظات العميل:</strong> {{ $order->notes }}</div> @endif
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-4">تحديث الحالة</h3>
            <form method="POST" action="{{ route('delivery.order.update', $order) }}">
                @csrf
                @method('PATCH')
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الحالة الجديدة</label>
                        <select name="status" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            <option value="">اختر الحالة...</option>
                            @php
                                $stateMachine = app(\App\Services\OrderStateMachine::class);
                                $available = $stateMachine->availableTransitions($order);
                            @endphp
                            @foreach($available as $status)
                                <option value="{{ $status }}">{{ \App\Services\OrderStateMachine::statusLabel($status, app()->getLocale()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ملاحظات التوصيل</label>
                        <textarea name="delivery_notes" rows="3" maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none resize-none"
                                  placeholder="مثال: تم التوصيل للجيربون / العميل مش موجود...">{{ $order->delivery_notes }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl transition text-sm">
                        تحديث الحالة
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">معلومات الدفع</h3>
            <div class="text-sm text-gray-600 space-y-1">
                <div class="flex justify-between"><span>طريقة الدفع</span><span class="font-medium">{{ $order->payment_method }}</span></div>
                <div class="flex justify-between"><span>حالة الدفع</span><span class="font-medium">{{ $order->payment_status }}</span></div>
                <div class="flex justify-between"><span>المبلغ</span><span class="font-bold text-gray-900">{{ number_format($order->total, 0) }} ج.م</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
