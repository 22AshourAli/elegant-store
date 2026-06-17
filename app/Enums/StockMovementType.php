<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Sale = 'sale';
    case Return = 'return';
    case Exchange = 'exchange';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case Adjustment = 'adjustment';
    case Initial = 'initial';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
