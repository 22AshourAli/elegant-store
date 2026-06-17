<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Wallet = 'wallet';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
