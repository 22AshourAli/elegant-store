<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\District;
use App\Models\Governorate;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index()
    {
        $districts = District::with('governorate', 'city')->orderBy('name')->paginate(20);
        return view('admin.districts.index', compact('districts'));
    }

    public function create()
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $cities = City::where('is_active', true)->orderBy('name')->get();
        return view('admin.districts.create', compact('governorates', 'cities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        District::create($data);
        return redirect()->route('admin.districts.index')->with('success', __('global.admin_district_created'));
    }

    public function edit(District $district)
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $cities = City::where('is_active', true)->orderBy('name')->get();
        return view('admin.districts.edit', compact('district', 'governorates', 'cities'));
    }

    public function update(Request $request, District $district)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $district->update($data);
        return redirect()->route('admin.districts.index')->with('success', __('global.admin_district_updated'));
    }

    public function destroy(District $district)
    {
        $district->delete();
        return redirect()->route('admin.districts.index')->with('success', __('global.admin_district_deleted'));
    }
}
