<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function index()
    {
        $carriers = Carrier::orderBy('name')->paginate(20);
        return view('admin.carriers.index', compact('carriers'));
    }

    public function create()
    {
        return view('admin.carriers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:50|unique:carriers,code',
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        Carrier::create($data);

        return redirect()->route('admin.carriers.index')->with('success', __('global.created_success'));
    }

    public function edit(Carrier $carrier)
    {
        return view('admin.carriers.edit', compact('carrier'));
    }

    public function update(Request $request, Carrier $carrier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:50|unique:carriers,code,' . $carrier->id,
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $carrier->update($data);

        return redirect()->route('admin.carriers.index')->with('success', __('global.updated_success'));
    }

    public function destroy(Carrier $carrier)
    {
        $carrier->delete();

        return redirect()->route('admin.carriers.index')->with('success', 'تم حذف شركة الشحن بنجاح.');
    }
}
