<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(15);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        Supplier::create($data);

        return redirect()->route('admin.suppliers.index')
            ->with('success', __('global.supplier_created'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $supplier->update($data);

        return redirect()->route('admin.suppliers.index')
            ->with('success', __('global.supplier_updated'));
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', __('global.supplier_deleted'));
    }
}
