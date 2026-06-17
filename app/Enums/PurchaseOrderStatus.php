<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case PartiallyReceived = 'partially_received';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
