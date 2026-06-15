<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
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

    public function cities(Request $request)
    {
        $request->validate(['governorate_id' => 'required|exists:governorates,id']);

        return response()->json(
            City::where('governorate_id', $request->governorate_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'nullable|exists:cities,id',
            'cart_total' => 'required|numeric|min:0',
        ]);

        $result = $this->shippingService->calculateCost(
            $validated['governorate_id'],
            $validated['city_id'],
            $validated['cart_total'],
        );

        return response()->json($result);
    }
}
