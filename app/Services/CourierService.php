<?php

namespace App\Services;

use App\Models\Order;

interface CourierService
{
    public function createWaybill(Order $order): array;
    public function trackShipment(string $trackingNumber): array;
    public function cancelShipment(string $trackingNumber): bool;
    public function getWebhookPayload(array $data): array;
    public function name(): string;
}
