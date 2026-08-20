@extends('admin.layouts.app')

@section('title', 'جرد المخزون')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900">جرد المخزون</h1>
        <a href="{{ route('admin.inventory.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
            + جرد جديد
        </a>
    </div>

    @if($counts->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400 text-sm">لا توجد عمليات جرد بعد</div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-right px-4 py-3 font-bold text-gray-600">#</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600">الفرع</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600">المحرر</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600">الحالة</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-600">التاريخ</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($counts as $count)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $count->id }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $count->branch->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $count->counter->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'in_progress' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-green-100 text-green-700'];
                                @endphp
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $statusColors[$count->status] ?? '' }}">
                                    {{ match($count->status) { 'draft' => 'مسودة', 'in_progress' => 'قيد التنفيذ', 'completed' => 'مكتمل', default => $count->status } }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $count->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.inventory.show', $count) }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-bold">عرض</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $counts->links() }}</div>
    @endif
</div>
@endsection
