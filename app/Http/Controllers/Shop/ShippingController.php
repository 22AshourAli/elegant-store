<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use App\Models\City;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        protected ShippingService $shippingService
    ) {}

    public function getCities(Request $request)
    {
        $request->validate(['governorate_id' => 'required|exists:governorates,id']);

        $cities = City::where('governorate_id', $request->governorate_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function calculateCost(Request $request)
    {
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'cart_total' => 'nullable|numeric|min:0',
        ]);

        $result = $this->shippingService->calculateCost(
            governorateId: $request->governorate_id,
            cityId: $request->city_id,
            cartTotal: $request->cart_total ?? 0,
        );

        return response()->json($result);
    }
}
