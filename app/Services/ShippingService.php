<?php

namespace App\Services;

use App\Models\Governorate;
use App\Models\ShippingRate;

class ShippingService
{
    /**
     * Calculate shipping cost for a given governorate/city and cart total.
     *
     * Priority:
     * 1. City-specific rate (most specific)
     * 2. Governorate-wide rate
     * 3. Default fallback rate
     */
    public function calculateCost(
        int $governorateId,
        ?int $cityId,
        float $cartTotal = 0
    ): array {
        $rate = null;

        // Try city-specific rate first
        if ($cityId) {
            $rate = ShippingRate::where('governorate_id', $governorateId)
                ->where('city_id', $cityId)
                ->where('is_active', true)
                ->first();
        }

        // Fall back to governorate-wide rate
        if (!$rate) {
            $rate = ShippingRate::where('governorate_id', $governorateId)
                ->whereNull('city_id')
                ->where('is_active', true)
                ->first();
        }

        // Fall back to any rate for this governorate
        if (!$rate) {
            $rate = ShippingRate::where('governorate_id', $governorateId)
                ->where('is_active', true)
                ->first();
        }

        // Check for free shipping eligibility
        if ($rate && $rate->min_cart_amount && $cartTotal >= $rate->min_cart_amount) {
            return [
                'cost' => 0.0,
                'method' => 'free',
                'label' => 'Free Shipping',
                'rate_id' => $rate->id,
            ];
        }

        $cost = $rate ? (float) $rate->rate : config('shipping.default_rate', 50);

        return [
            'cost' => $cost,
            'method' => $cost > 0 ? 'flat' : 'free',
            'label' => $cost > 0 ? "Shipping: {$cost} EGP" : 'Free Shipping',
            'rate_id' => $rate?->id,
        ];
    }

    /**
     * Get all active governorates with their cities for checkout dropdowns.
     */
    public function getCheckoutLocations(): array
    {
        return Governorate::with(['cities' => fn($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'cities' => $g->cities->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'delivery_time' => $c->delivery_time,
                ]),
            ])
            ->toArray();
    }

    /**
     * Placeholder for future courier API integration.
     * Implement specific courier logic in dedicated classes (e.g., BostaCourier, AramexCourier).
     */
    public function createShipment(array $orderData): array
    {
        // Future: dispatch to CourierServiceFactory::driver($courierName)->createShipment($orderData);
        return [
            'tracking_number' => null,
            'tracking_url' => null,
            'courier_name' => null,
        ];
    }

    /**
     * Placeholder for tracking status updates via courier webhook/polling.
     */
    public function trackShipment(string $trackingNumber, string $courier): ?array
    {
        // Future: CourierServiceFactory::driver($courier)->track($trackingNumber);
        return null;
    }
}
