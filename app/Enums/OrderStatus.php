<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Cancelled = 'cancelled';
    case Collected = 'collected';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
