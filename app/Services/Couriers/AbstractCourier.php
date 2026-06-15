<?php

namespace App\Services\Couriers;

use App\Models\Order;
use App\Services\CourierService;

abstract class AbstractCourier implements CourierService
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    protected function buildAddressString(Order $order): string
    {
        if ($order->address_street) {
            $parts = array_filter([
                $order->governorate?->name,
                $order->city?->name,
                $order->address_street,
                $order->address_building ? "Building {$order->address_building}" : null,
                $order->address_floor ? "Floor {$order->address_floor}" : null,
                $order->address_apartment ? "Apartment {$order->address_apartment}" : null,
                $order->address_landmark ? "Near: {$order->address_landmark}" : null,
            ]);
            return implode(', ', $parts);
        }
        return $order->shipping_address ?? '';
    }
}
