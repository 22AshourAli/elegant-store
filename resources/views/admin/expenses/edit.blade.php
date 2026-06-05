@extends('admin.layouts.app')
@section('page-title', 'تعديل المصروف')
@section('content')
<div class="max-w-lg mx-auto bg-white dark:bg-gray-800 rounded shadow p-6">
    <form action="{{ route('admin.expenses.update', $expense) }}" method="POST">
        @csrf @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">التصنيف <span class="text-red-500">*</span></label>
                <select name="category" required class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $expense->category === $cat ? 'selected' : '' }}>{{ \App\Models\Expense::categoryLabel($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">الوصف <span class="text-red-500">*</span></label>
                <textarea name="description" rows="2" required class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600">{{ $expense->description }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">المبلغ <span class="text-red-500">*</span></label>
                <input type="number" name="amount" step="0.01" min="0" value="{{ $expense->amount }}" required class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">التاريخ <span class="text-red-500">*</span></label>
                <input type="date" name="expense_date" value="{{ $expense->expense_date->format('Y-m-d') }}" required class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">الفرع</label>
                <select name="branch_id" class="w-full border rounded px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600">
                    <option value="">—</option>
                    @foreach(\App\Models\Branch::all() as $branch)
                        <option value="{{ $branch->id }}" {{ $expense->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2.5 rounded hover:bg-indigo-700 transition text-sm">تحديث المصروف</button>
        </div>
    </form>
</div>
@endsection
