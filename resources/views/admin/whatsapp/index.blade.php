@extends('admin.layouts.app')
@section('page-title', 'التسويق عبر واتساب')
@section('content')
<div class="mb-4 flex gap-2">
    <a href="{{ route('admin.whatsapp.bulk') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        إرسال جماعي
    </a>
    <a href="{{ route('admin.whatsapp.next') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-600 text-white font-bold rounded-lg hover:bg-gray-700 transition text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
        التالي في الخط
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm text-right">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="p-3">العميل</th>
                <th class="p-3 hidden md:table-cell">إجمالي الطلبات</th>
                <th class="p-3 hidden md:table-cell">إجمالي الإنفاق</th>
                <th class="p-3 hidden md:table-cell">آخر رسالة</th>
                <th class="p-3 hidden md:table-cell">آخر إرسال</th>
                <th class="p-3">الإجراء</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                @php
                    $log = $latestLogs[$customer->id] ?? null;
                @endphp
                <tr class="border-t dark:border-gray-700 even:bg-gray-50/50 dark:even:bg-gray-700/20">
                    <td class="p-3">
                            <div class="flex items-center gap-1.5">
                                <span>{{ $customer->name }}</span>
                                @if(!empty($customer->email))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">أونلاين</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">أوفلاين</span>
                                @endif
                                @if($customer->total_orders > 0)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">مشتري</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">جديد</span>
                                @endif
                            </div>
                        </td>
                    <td class="p-3 hidden md:table-cell">{{ $customer->total_orders }}</td>
                    <td class="p-3 hidden md:table-cell">{{ number_format((int) $customer->total_spent) }} ج.م</td>
                    <td class="p-3 max-w-xs truncate hidden md:table-cell">
                        @if($log)
                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $log->status === 'sent' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $log->status === 'sent' ? 'مرسلة' : 'معلقة' }}
                            </span>
                            <span class="text-xs text-gray-500 block mt-1">{{ Str::limit($log->message, 40) }}</span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="p-3 text-xs text-gray-500 hidden md:table-cell">
                        {{ $log ? $log->sent_at?->format('Y-m-d H:i') : '-' }}
                    </td>
                    <td class="p-3 text-right">
                        <a href="{{ route('admin.whatsapp.show', ['user' => $customer->id]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            إرسال رسالة
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-gray-400">لا يوجد عملاء</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $customers->onEachSide(1)->links('vendor.pagination.admin') }}</div>
@endsection
