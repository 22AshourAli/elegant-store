<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);
        
        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        $data['is_active'] = $request->has('is_active');
        
        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'تم إضافة التصنيف');
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);
        
        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        $data['is_active'] = $request->has('is_active');

        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'تم التحديث');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'تم الحذف');
    }
}
