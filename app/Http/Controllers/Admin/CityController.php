<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('governorate')->orderBy('name')->paginate(20);
        return view('admin.cities.index', compact('cities'));
    }

    public function create()
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        return view('admin.cities.create', compact('governorates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:255',
            'delivery_time' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        City::create($data);
        return redirect()->route('admin.cities.index')->with('success', __('global.admin_city_created'));
    }

    public function edit(City $city)
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        return view('admin.cities.edit', compact('city', 'governorates'));
    }

    public function update(Request $request, City $city)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:255',
            'delivery_time' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $city->update($data);
        return redirect()->route('admin.cities.index')->with('success', __('global.admin_city_updated'));
    }

    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->route('admin.cities.index')->with('success', __('global.admin_city_deleted'));
    }
}
