<?php

namespace App\Services;

use App\Models\City;
use App\Models\District;
use App\Models\Governorate;
use App\Models\ShippingRate;

class ShippingService
{
    public function calculateCost(
        int $governorateId,
        ?int $cityId = null,
        ?int $districtId = null,
        float $cartTotal = 0,
    ): array {
        $rate = null;

        // Priority 1: district-specific rate
        if ($districtId) {
            $rate = ShippingRate::where('governorate_id', $governorateId)
                ->where('city_id', $cityId)
                ->where('district_id', $districtId)
                ->where('is_active', true)
                ->first();
        }

        // Priority 2: city-specific rate
        if (!$rate && $cityId) {
            $rate = ShippingRate::where('governorate_id', $governorateId)
                ->where('city_id', $cityId)
                ->whereNull('district_id')
                ->where('is_active', true)
                ->first();
        }

        // Priority 3: governorate-wide rate
        if (!$rate) {
            $rate = ShippingRate::where('governorate_id', $governorateId)
                ->whereNull('city_id')
                ->whereNull('district_id')
                ->where('is_active', true)
                ->first();
        }

        // Priority 4: any rate for this governorate
        if (!$rate) {
            $rate = ShippingRate::where('governorate_id', $governorateId)
                ->where('is_active', true)
                ->first();
        }

        // Free shipping eligibility
        if ($rate && $rate->min_cart_amount && $cartTotal >= $rate->min_cart_amount) {
            return [
                'cost' => 0.0,
                'method' => 'free',
                'label' => __('global.free'),
                'rate_id' => $rate->id,
            ];
        }

        $cost = $rate ? (float) $rate->rate : (float) config('shipping.default_rate', 50);

        return [
            'cost' => $cost,
            'method' => $cost > 0 ? 'flat' : 'free',
            'label' => $cost > 0 ? number_format($cost, 2) . ' ' . __('global.currency') : __('global.free'),
            'rate_id' => $rate?->id,
        ];
    }

    public function getCheckoutLocations(): array
    {
        return Governorate::with([
            'cities' => fn($q) => $q->where('is_active', true)->orderBy('name'),
            'cities.districts' => fn($q) => $q->where('is_active', true)->orderBy('name'),
        ])
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
                    'districts' => $c->districts->map(fn($d) => [
                        'id' => $d->id,
                        'name' => $d->name,
                        'type' => $d->type,
                    ]),
                ]),
            ])
            ->toArray();
    }

    public function createShipment(array $orderData): array
    {
        return [
            'tracking_number' => null,
            'tracking_url' => null,
            'courier_name' => null,
        ];
    }

    public function trackShipment(string $trackingNumber, string $courier): ?array
    {
        return null;
    }
}
