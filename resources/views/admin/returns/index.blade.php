@extends('admin.layouts.app')
@section('page-title', 'طلبات الإرجاع')
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm text-right">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="p-3">#</th>
                <th class="p-3">العميل</th>
                <th class="p-3">الطلب</th>
                <th class="p-3">السبب</th>
                <th class="p-3">الحالة</th>
                <th class="p-3">التاريخ</th>
                <th class="p-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($returns as $return)
                <tr class="border-t dark:border-gray-700">
                    <td class="p-3">{{ $return->id }}</td>
                    <td class="p-3">{{ $return->user->name }}</td>
                    <td class="p-3">#{{ $return->order_id }}</td>
                    <td class="p-3 max-w-xs truncate">{{ Str::limit($return->reason, 60) }}</td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold
                            @if($return->status === 'pending') bg-amber-100 text-amber-700
                            @elseif($return->status === 'approved') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ $return->status }}
                        </span>
                    </td>
                    <td class="p-3 text-xs text-gray-500">{{ $return->created_at->format('Y-m-d') }}</td>
                    <td class="p-3">
                        <a href="{{ route('admin.returns.show', $return) }}" class="text-indigo-600 hover:underline text-xs font-bold">عرض</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-6 text-center text-gray-400">لا توجد طلبات</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $returns->links() }}</div>
@endsection
