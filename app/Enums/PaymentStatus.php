<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
