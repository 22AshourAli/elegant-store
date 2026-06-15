<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class ShippingSettingsController extends Controller
{
    public function index()
    {
        return view('admin.shipping-settings.index');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'fuel_surcharge_percentage' => 'required|numeric|min:0|max:100',
            'free_shipping_threshold' => 'required|numeric|min:0',
            'default_shipping_cost' => 'required|numeric|min:0',
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        ShippingService::clearCache();

        return redirect()->route('admin.shipping-settings.index')
            ->with('success', __('global.updated_success'));
    }
}
