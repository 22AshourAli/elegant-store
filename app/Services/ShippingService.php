<?php

namespace App\Services;

use App\Models\Governorate;
use App\Models\ShippingRate;
use Illuminate\Support\Facades\Cache;

class ShippingService
{
    public function calculateCost(int $governorateId, ?int $cityId = null, float $cartTotal = 0): array
    {
        $governorate = Governorate::findOrFail($governorateId);
        $fuelSurcharge = $this->getFuelSurcharge();

        $rate = ShippingRate::where('governorate_id', $governorateId)
            ->where('is_active', true)
            ->where(function ($q) use ($cityId) {
                $q->where('city_id', $cityId)->orWhereNull('city_id');
            })
            ->orderByRaw('city_id IS NULL ASC')
            ->first();

        $baseCost = $rate?->rate ?? $governorate->base_shipping_cost;

        if ($rate && $rate->min_cart_amount && $cartTotal >= $rate->min_cart_amount) {
            return [
                'cost' => 0,
                'base_cost' => $baseCost,
                'fuel_surcharge' => 0,
                'final_cost' => 0,
                'is_free' => true,
                'reason' => 'cart_threshold',
            ];
        }

        $surchargeAmount = $baseCost * ($fuelSurcharge / 100);
        $finalCost = $baseCost + $surchargeAmount;

        return [
            'cost' => round($finalCost, 2),
            'base_cost' => round($baseCost, 2),
            'fuel_surcharge' => round($surchargeAmount, 2),
            'final_cost' => round($finalCost, 2),
            'is_free' => false,
            'reason' => null,
        ];
    }

    public function getCheckoutLocations()
    {
        return Cache::remember('checkout_locations', 300, fn() =>
            Governorate::where('is_active', true)
                ->with(['cities' => fn($q) => $q->where('is_active', true)->orderBy('name')])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($gov) => [
                    'id' => $gov->id,
                    'name' => $gov->name,
                    'cities' => $gov->cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                ])
        );
    }

    public function getFuelSurcharge(): float
    {
        $val = \App\Models\Setting::getValue('fuel_surcharge_percentage', '0');
        return max(0, (float) $val);
    }

    public function getFreeShippingThreshold(): float
    {
        $val = \App\Models\Setting::getValue('free_shipping_threshold', '500');
        return max(0, (float) $val);
    }

    public function getDefaultShippingCost(): float
    {
        $val = \App\Models\Setting::getValue('default_shipping_cost', '30');
        return max(0, (float) $val);
    }

    public static function clearCache(): void
    {
        Cache::forget('checkout_locations');
    }
}
