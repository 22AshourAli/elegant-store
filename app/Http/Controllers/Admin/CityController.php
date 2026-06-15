<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Governorate;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with('governorate');
        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }
        $cities = $query->orderBy('name')->paginate(20);
        $governorates = Governorate::orderBy('name')->get();
        return view('admin.cities.index', compact('cities', 'governorates'));
    }

    public function create()
    {
        $governorates = Governorate::orderBy('name')->get();
        return view('admin.cities.create', compact('governorates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        City::create($data);
        ShippingService::clearCache();

        return redirect()->route('admin.cities.index')->with('success', __('global.created_success'));
    }

    public function edit(City $city)
    {
        $governorates = Governorate::orderBy('name')->get();
        return view('admin.cities.edit', compact('city', 'governorates'));
    }

    public function update(Request $request, City $city)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $city->update($data);
        ShippingService::clearCache();

        return redirect()->route('admin.cities.index')->with('success', __('global.updated_success'));
    }

    public function toggleActive(City $city)
    {
        $city->update(['is_active' => !$city->is_active]);
        ShippingService::clearCache();

        return back()->with('success', __('global.updated_success'));
    }
}
