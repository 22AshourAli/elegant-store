<?php

namespace App\Contracts;

interface CourierDriver
{
    /**
     * Create a shipment and return tracking info.
     *
     * @param array $params [address, phone, name, items, weight, ...]
     * @return array [tracking_number, tracking_url, courier_name]
     */
    public function createShipment(array $params): array;

    /**
     * Track an existing shipment by tracking number.
     *
     * @return array [status, estimated_delivery, events[]]
     */
    public function track(string $trackingNumber): ?array;
}
