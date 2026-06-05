<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Expense::with('branch', 'createdBy');

        if ($user->isManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        if (request('category')) {
            $query->where('category', request('category'));
        }

        $expenses = $query->latest('expense_date')->paginate(20);
        $total = (clone $query)->sum('amount');
        $categories = Expense::CATEGORIES;

        return view('admin.expenses.index', compact('expenses', 'total', 'categories'));
    }

    public function create()
    {
        $categories = Expense::CATEGORIES;
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', Expense::CATEGORIES),
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['created_by'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'تم إضافة المصروف بنجاح.');
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::CATEGORIES;
        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', Expense::CATEGORIES),
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $expense->update($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'تم تحديث المصروف بنجاح.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', 'تم حذف المصروف بنجاح.');
    }
}
