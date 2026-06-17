<?php

namespace App\Enums;

enum StockTransferStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
