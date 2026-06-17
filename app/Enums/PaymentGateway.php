<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Paymob = 'paymob';
    case Cash = 'cash';
    case Fawry = 'fawry';
    case Credit = 'credit';
    case Wallet = 'wallet';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
