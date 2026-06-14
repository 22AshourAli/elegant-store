<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingProvider;
use Illuminate\Http\Request;

class ShippingProviderController extends Controller
{
    public function index()
    {
        $providers = ShippingProvider::orderBy('name')->paginate(15);
        return view('admin.shipping_providers.index', compact('providers'));
    }

    public function create()
    {
        return view('admin.shipping_providers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        ShippingProvider::create($data);
        return redirect()->route('admin.shipping-providers.index')->with('success', __('global.admin_shipping_provider_created'));
    }

    public function edit(ShippingProvider $shippingProvider)
    {
        return view('admin.shipping_providers.edit', compact('shippingProvider'));
    }

    public function update(Request $request, ShippingProvider $shippingProvider)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $shippingProvider->update($data);
        return redirect()->route('admin.shipping-providers.index')->with('success', __('global.admin_shipping_provider_updated'));
    }

    public function destroy(ShippingProvider $shippingProvider)
    {
        $shippingProvider->delete();
        return redirect()->route('admin.shipping-providers.index')->with('success', __('global.admin_shipping_provider_deleted'));
    }
}
