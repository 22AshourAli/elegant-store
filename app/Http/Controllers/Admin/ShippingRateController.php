<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Governorate;
use App\Models\ShippingRate;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function index()
    {
        $rates = ShippingRate::with('governorate', 'city')->orderBy('governorate_id')->paginate(20);
        $governorates = Governorate::orderBy('name')->get();
        return view('admin.shipping-rates.index', compact('rates', 'governorates'));
    }

    public function create()
    {
        $governorates = Governorate::orderBy('name')->get();
        return view('admin.shipping-rates.create', compact('governorates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'rate' => 'required|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        ShippingRate::create($data);
        ShippingService::clearCache();

        return redirect()->route('admin.shipping-rates.index')->with('success', __('global.created_success'));
    }

    public function edit(ShippingRate $shippingRate)
    {
        $governorates = Governorate::orderBy('name')->get();
        $cities = City::where('governorate_id', $shippingRate->governorate_id)->where('is_active', true)->orderBy('name')->get();
        return view('admin.shipping-rates.edit', compact('shippingRate', 'governorates', 'cities'));
    }

    public function update(Request $request, ShippingRate $shippingRate)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'rate' => 'required|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $shippingRate->update($data);
        ShippingService::clearCache();

        return redirect()->route('admin.shipping-rates.index')->with('success', __('global.updated_success'));
    }

    public function destroy(ShippingRate $shippingRate)
    {
        $shippingRate->delete();
        ShippingService::clearCache();

        return redirect()->route('admin.shipping-rates.index')->with('success', __('global.deleted_success'));
    }

    // API: get cities by governorate for dynamic dropdown
    public function getCities(Request $request)
    {
        $cities = City::where('governorate_id', $request->governorate_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($cities);
    }
}
