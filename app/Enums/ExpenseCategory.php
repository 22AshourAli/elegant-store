<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Salaries = 'salaries';
    case Rent = 'rent';
    case DamagedItems = 'damaged_items';
    case Utilities = 'utilities';
    case Other = 'other';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
