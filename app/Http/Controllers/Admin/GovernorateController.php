<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class GovernorateController extends Controller
{
    public function index()
    {
        $governorates = Governorate::withCount('cities')->orderBy('name')->paginate(20);
        return view('admin.governorates.index', compact('governorates'));
    }

    public function create()
    {
        return view('admin.governorates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'base_shipping_cost' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        Governorate::create($data);
        ShippingService::clearCache();

        return redirect()->route('admin.governorates.index')->with('success', __('global.created_success'));
    }

    public function edit(Governorate $governorate)
    {
        return view('admin.governorates.edit', compact('governorate'));
    }

    public function update(Request $request, Governorate $governorate)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'base_shipping_cost' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $governorate->update($data);
        ShippingService::clearCache();

        return redirect()->route('admin.governorates.index')->with('success', __('global.updated_success'));
    }

    public function toggleActive(Governorate $governorate)
    {
        $governorate->update(['is_active' => !$governorate->is_active]);
        ShippingService::clearCache();

        return back()->with('success', __('global.updated_success'));
    }

    public function destroy(Governorate $governorate)
    {
        if ($governorate->cities()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المحافظة لأنها تحتوي على مدن. قم بحذف المدن أولاً أو تعطيل المحافظة بدلاً من ذلك.');
        }
        $governorate->delete();
        ShippingService::clearCache();

        return redirect()->route('admin.governorates.index')->with('success', __('global.deleted_success'));
    }
}
