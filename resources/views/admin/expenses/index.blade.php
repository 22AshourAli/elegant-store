@extends('admin.layouts.app')
@section('page-title', __('global.expenses'))
@section('content')
<div class="mb-4 flex items-center justify-between">
    <div class="text-sm text-gray-500">{{ __('global.total_expenses') }}: <span class="font-bold text-red-600">{{ (int) round($total) }} {{ __('global.currency') }}</span></div>
    <a href="{{ route('admin.expenses.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition">{{ __('global.add_expense') }}</a>
</div>
<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm text-right">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr><th class="p-3">#</th><th class="p-3">{{ __('global.category') }}</th><th class="p-3 hidden md:table-cell">{{ __('global.description') }}</th><th class="p-3">{{ __('global.amount') }}</th><th class="p-3 hidden md:table-cell">{{ __('global.date') }}</th><th class="p-3 hidden md:table-cell">{{ __('global.branch') }}</th><th class="p-3 hidden md:table-cell">{{ __('global.added_by') }}</th><th class="p-3"></th></tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr class="border-t dark:border-gray-700 even:bg-gray-50/50 dark:even:bg-gray-700/20">
                <td class="p-3">{{ $expense->id }}</td>
                <td class="p-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700">{{ \App\Models\Expense::categoryLabel($expense->category) }}</span></td>
                <td class="p-3 max-w-xs truncate hidden md:table-cell">{{ $expense->description }}</td>
                <td class="p-3 font-bold">{{ (int) round($expense->amount) }} {{ __('global.currency') }}</td>
                <td class="p-3 text-xs text-gray-500 hidden md:table-cell">{{ $expense->expense_date->format('Y-m-d') }}</td>
                <td class="p-3 text-xs hidden md:table-cell">{{ $expense->branch?->name ?? '—' }}</td>
                <td class="p-3 text-xs hidden md:table-cell">{{ $expense->createdBy?->name ?? '—' }}</td>
                <td class="p-3 text-right"><a href="{{ route('admin.expenses.edit', $expense) }}" class="text-indigo-600 hover:underline text-xs font-bold">{{ __('global.edit') }}</a>
                    <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('global.delete_expense_confirm') }}')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-xs font-bold mr-2">{{ __('global.delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="p-6 text-center text-gray-400">{{ __('global.no_expenses') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $expenses->onEachSide(1)->links('vendor.pagination.admin') }}</div>
@endsection
