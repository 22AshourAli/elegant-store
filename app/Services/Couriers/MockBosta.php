<?php

namespace App\Services\Couriers;

use App\Models\Order;

class MockBosta extends AbstractCourier
{
    public function createWaybill(Order $order): array
    {
        return [
            'success' => true,
            'tracking_number' => 'BOSTA-MOCK-' . strtoupper(uniqid()),
            'waybill_url' => null,
            'label_url' => null,
            'courier_name' => 'bosta',
            'estimated_delivery' => now()->addDays(3)->toDateString(),
            'raw_response' => ['mock' => true, 'message' => 'Mock Bosta waybill created'],
        ];
    }

    public function trackShipment(string $trackingNumber): array
    {
        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'status' => 'in_transit',
            'last_update' => now()->toIso8601String(),
            'events' => [
                ['date' => now()->subDay()->toIso8601String(), 'status' => 'pending_pickup', 'location' => 'Warehouse'],
                ['date' => now()->toIso8601String(), 'status' => 'in_transit', 'location' => 'Sorting Center'],
            ],
            'raw_response' => ['mock' => true],
        ];
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        return true;
    }

    public function getWebhookPayload(array $data): array
    {
        return [
            'event' => $data['event'] ?? 'status_updated',
            'tracking_number' => $data['tracking'] ?? '',
            'status' => $data['status'] ?? 'delivered',
        ];
    }

    public function name(): string
    {
        return 'bosta';
    }
}
