<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        private readonly ShippingService $shippingService
    ) {}

    public function locations()
    {
        return response()->json([
            'data' => $this->shippingService->getCheckoutLocations(),
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'cart_total' => 'required|numeric|min:0',
        ]);

        $result = $this->shippingService->calculateCost(
            governorateId: (int) $validated['governorate_id'],
            cityId: !empty($validated['city_id']) ? (int) $validated['city_id'] : null,
            districtId: !empty($validated['district_id']) ? (int) $validated['district_id'] : null,
            cartTotal: (float) $validated['cart_total'],
        );

        return response()->json($result);
    }
}
