<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\District;
use App\Models\Governorate;
use App\Models\ShippingProvider;
use App\Models\ShippingRate;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function index()
    {
        $rates = ShippingRate::with('governorate', 'city', 'district', 'shippingProvider')
            ->orderBy('governorate_id')
            ->paginate(20);
        return view('admin.shipping_rates.index', compact('rates'));
    }

    public function create()
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $providers = ShippingProvider::orderBy('name')->get();
        return view('admin.shipping_rates.create', compact('governorates', 'providers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'shipping_provider_id' => 'nullable|exists:shipping_providers,id',
            'rate' => 'required|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        ShippingRate::create($data);
        return redirect()->route('admin.shipping-rates.index')->with('success', __('global.admin_shipping_rate_created'));
    }

    public function edit(ShippingRate $shippingRate)
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $providers = ShippingProvider::orderBy('name')->get();
        $cities = City::where('governorate_id', $shippingRate->governorate_id)->where('is_active', true)->orderBy('name')->get();
        $districts = District::where('governorate_id', $shippingRate->governorate_id)->where('city_id', $shippingRate->city_id)->where('is_active', true)->orderBy('name')->get();
        return view('admin.shipping_rates.edit', compact('shippingRate', 'governorates', 'providers', 'cities', 'districts'));
    }

    public function update(Request $request, ShippingRate $shippingRate)
    {
        $data = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'shipping_provider_id' => 'nullable|exists:shipping_providers,id',
            'rate' => 'required|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $shippingRate->update($data);
        return redirect()->route('admin.shipping-rates.index')->with('success', __('global.admin_shipping_rate_updated'));
    }

    public function destroy(ShippingRate $shippingRate)
    {
        $shippingRate->delete();
        return redirect()->route('admin.shipping-rates.index')->with('success', __('global.admin_shipping_rate_deleted'));
    }
}
