@extends('admin.layouts.app')
@section('page-title', $return->type === 'exchange' ? 'طلب استبدال #' . $return->id : 'طلب إرجاع #' . $return->id)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
            <h3 class="font-bold mb-4">تفاصيل الطلب</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="font-semibold">النوع:</span>
                    <span class="px-2 py-0.5 rounded text-xs font-bold {{ $return->type === 'exchange' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $return->type === 'exchange' ? 'استبدال' : 'إرجاع' }}
                    </span>
                </div>
                <div><span class="font-semibold">العميل:</span> {{ $return->user->name }} ({{ $return->user->email }})</div>
                <div><span class="font-semibold">الطلب رقم:</span> #{{ $return->order_id }}</div>
                <div><span class="font-semibold">الحالة:</span> {{ $return->status }}</div>
                <div><span class="font-semibold">تاريخ الطلب:</span> {{ $return->created_at->format('Y-m-d H:i') }}</div>
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded">
                    <span class="font-semibold block mb-1">السبب:</span>
                    <p class="text-gray-600 dark:text-gray-300">{{ $return->reason }}</p>
                </div>
                @if($return->admin_note)
                    <div class="mt-4 p-4 bg-indigo-50 dark:bg-indigo-950/20 rounded">
                        <span class="font-semibold block mb-1">ملاحظة الإدارة:</span>
                        <p class="text-indigo-700 dark:text-indigo-300">{{ $return->admin_note }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($return->type === 'exchange' && $return->exchange_data)
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="font-bold mb-4">تفاصيل الاستبدال</h3>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr><th class="p-2 text-right">المنتج الأصلي</th><th class="p-2">الكمية</th><th class="p-2">المنتج الجديد</th></tr>
                    </thead>
                    <tbody>
                        @foreach($return->exchange_data as $exchangeItem)
                            @php
                                $orderItem = $return->order->items->find($exchangeItem['order_item_id']);
                                $newVariant = \App\Models\ProductVariant::with('product')->find($exchangeItem['new_variant_id']);
                            @endphp
                            <tr class="border-t dark:border-gray-700">
                                <td class="p-2">
                                    {{ $orderItem->product_name ?? '#' . $exchangeItem['order_item_id'] }}
                                    @if($orderItem && $orderItem->color) ({{ $orderItem->color }}) @endif
                                </td>
                                <td class="p-2">{{ $orderItem->quantity ?? '-' }}</td>
                                <td class="p-2">
                                    @if($newVariant)
                                        {{ $newVariant->product->name ?? '' }} #{{ $newVariant->id }}
                                        @if($newVariant->color) ({{ $newVariant->color }}) @endif
                                        @if($newVariant->size) | {{ $newVariant->size }} @endif
                                    @else
                                        <span class="text-red-500">محذوف</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
            <h3 class="font-bold mb-4">منتجات الطلب</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr><th class="p-2 text-right">المنتج</th><th class="p-2">الكمية</th><th class="p-2">السعر</th></tr>
                </thead>
                <tbody>
                    @foreach($return->order->items as $item)
                    <tr class="border-t dark:border-gray-700">
                        <td class="p-2">{{ $item->product_name }} @if($item->color) ({{ $item->color }}) @endif</td>
                        <td class="p-2">{{ $item->quantity }}</td>
                        <td class="p-2">{{ (int) round($item->total) }} ج.م</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        @if($return->status === 'pending')
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6 space-y-4">
                <h3 class="font-bold">الإجراء</h3>
                <form action="{{ route('admin.returns.approve', $return) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">ملاحظة (اختياري)</label>
                        <textarea name="admin_note" rows="2" class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-2.5 rounded hover:bg-green-700 transition text-sm">
                        {{ $return->type === 'exchange' ? 'الموافقة على الاستبدال' : 'الموافقة على الإرجاع' }}
                    </button>
                </form>
                <form action="{{ route('admin.returns.reject', $return) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">سبب الرفض <span class="text-red-500">*</span></label>
                        <textarea name="admin_note" rows="2" required class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-red-600 text-white font-bold py-2.5 rounded hover:bg-red-700 transition text-sm">رفض الطلب</button>
                </form>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="font-bold mb-2">تمت المعالجة</h3>
                <p class="text-sm text-gray-500">تمت معالجة هذا الطلب بالفعل.</p>
            </div>
        @endif
    </div>
</div>
@endsection
