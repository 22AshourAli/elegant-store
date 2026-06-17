<?php

namespace App\Enums;

enum OrderType: string
{
    case Online = 'online';
    case Offline = 'offline';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
