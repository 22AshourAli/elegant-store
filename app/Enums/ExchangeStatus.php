<?php

namespace App\Enums;

enum ExchangeStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
