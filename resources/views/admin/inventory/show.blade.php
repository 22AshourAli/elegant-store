@extends('admin.layouts.app')

@section('title', "جرد #{$inventory->id}")

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">جرد #{{ $inventory->id }} - {{ $inventory->branch->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                الحالة:
                @php
                    $statusLabels = ['draft' => 'مسودة', 'in_progress' => 'قيد التنفيذ', 'completed' => 'مكتمل'];
                @endphp
                {{ $statusLabels[$inventory->status] ?? $inventory->status }}
                | المحرر: {{ $inventory->counter->name ?? '-' }}
            </p>
        </div>
        <div class="flex gap-2">
            @if($inventory->status === 'in_progress')
                <form method="POST" action="{{ route('admin.inventory.complete', $inventory) }}">
                    @csrf
                    @method('PATCH')
                    <button class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">إتمام الجرد</button>
                </form>
            @endif
            @if($inventory->status !== 'completed')
                <form method="POST" action="{{ route('admin.inventory.destroy', $inventory) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الجرد؟')">
                    @csrf
                    @method('DELETE')
                    <button class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-bold px-4 py-2 rounded-xl transition">حذف</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl p-3 text-center shadow-sm">
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_items'] }}</div>
            <div class="text-[10px] text-gray-400 font-bold">إجمالي الأصناف</div>
        </div>
        <div class="bg-white rounded-xl p-3 text-center shadow-sm">
            <div class="text-2xl font-bold text-green-600">{{ $stats['matched'] }}</div>
            <div class="text-[10px] text-gray-400 font-bold">متطابق</div>
        </div>
        <div class="bg-white rounded-xl p-3 text-center shadow-sm">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['over'] }}</div>
            <div class="text-[10px] text-gray-400 font-bold">زيادة</div>
        </div>
        <div class="bg-white rounded-xl p-3 text-center shadow-sm">
            <div class="text-2xl font-bold text-red-600">{{ $stats['under'] }}</div>
            <div class="text-[10px] text-gray-400 font-bold">نقص</div>
        </div>
        <div class="bg-white rounded-xl p-3 text-center shadow-sm">
            <div class="text-2xl font-bold text-gray-400">{{ $stats['pending'] }}</div>
            <div class="text-[10px] text-gray-400 font-bold">لم يُجرد</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-right px-4 py-2 font-bold text-gray-600 text-xs">المنتج</th>
                    <th class="text-right px-4 py-2 font-bold text-gray-600 text-xs">اللون</th>
                    <th class="text-right px-4 py-2 font-bold text-gray-600 text-xs">المقاس</th>
                    <th class="text-center px-4 py-2 font-bold text-gray-600 text-xs">_stock النظام</th>
                    <th class="text-center px-4 py-2 font-bold text-gray-600 text-xs">العدد الفعلي</th>
                    <th class="text-center px-4 py-2 font-bold text-gray-600 text-xs">الفرق</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($items as $item)
                    <tr class="hover:bg-gray-50 {{ $item->difference && $item->counted_stock !== null ? ($item->difference > 0 ? 'bg-blue-50/30' : 'bg-red-50/30') : '' }}">
                        <td class="px-4 py-2 font-medium text-gray-900 text-xs">{{ $item->variant->product->name }}</td>
                        <td class="px-4 py-2 text-gray-600 text-xs">{{ $item->variant->color ?? '-' }}</td>
                        <td class="px-4 py-2 text-gray-600 text-xs">{{ $item->variant->size ?? '-' }}</td>
                        <td class="px-4 py-2 text-center text-gray-600 text-xs font-mono">{{ $item->system_stock }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($inventory->status === 'completed')
                                <span class="text-xs font-mono {{ $item->counted_stock !== null ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $item->counted_stock ?? '-' }}
                                </span>
                            @else
                                <form method="POST" action="{{ route('admin.inventory.update-item', [$inventory, $item]) }}" class="inline-flex">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="counted_stock" value="{{ $item->counted_stock }}" min="0"
                                           class="w-16 px-2 py-1 border border-gray-200 rounded-lg text-center text-xs focus:ring-1 focus:ring-indigo-500 outline-none"
                                           placeholder="0">
                                </form>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center text-xs font-bold {{ $item->difference > 0 ? 'text-blue-600' : ($item->difference < 0 ? 'text-red-600' : 'text-gray-400') }}">
                            @if($item->counted_stock !== null)
                                {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($inventory->status !== 'completed' && $item->counted_stock !== null)
                                <button onclick="this.closest('form').submit()" class="text-green-600 hover:text-green-700 text-[10px] font-bold">حفظ</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
