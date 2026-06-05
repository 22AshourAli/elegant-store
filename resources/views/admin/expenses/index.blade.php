@extends('admin.layouts.app')
@section('page-title', 'المصروفات')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <div class="text-sm text-gray-500">إجمالي المصروفات: <span class="font-bold text-red-600">{{ (int) round($total) }} ج.م</span></div>
    <a href="{{ route('admin.expenses.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition">إضافة مصروف</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm text-right">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr><th class="p-3">#</th><th class="p-3">التصنيف</th><th class="p-3">الوصف</th><th class="p-3">المبلغ</th><th class="p-3">التاريخ</th><th class="p-3">الفرع</th><th class="p-3">أضيف بواسطة</th><th class="p-3"></th></tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr class="border-t dark:border-gray-700">
                <td class="p-3">{{ $expense->id }}</td>
                <td class="p-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700">{{ \App\Models\Expense::categoryLabel($expense->category) }}</span></td>
                <td class="p-3 max-w-xs truncate">{{ $expense->description }}</td>
                <td class="p-3 font-bold">{{ (int) round($expense->amount) }} ج.م</td>
                <td class="p-3 text-xs text-gray-500">{{ $expense->expense_date->format('Y-m-d') }}</td>
                <td class="p-3 text-xs">{{ $expense->branch?->name ?? '—' }}</td>
                <td class="p-3 text-xs">{{ $expense->createdBy?->name ?? '—' }}</td>
                <td class="p-3">
                    <a href="{{ route('admin.expenses.edit', $expense) }}" class="text-indigo-600 hover:underline text-xs font-bold">تعديل</a>
                    <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('حذف هذا المصروف؟')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-xs font-bold mr-2">حذف</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="p-6 text-center text-gray-400">لا توجد مصروفات</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $expenses->links() }}</div>
@endsection
