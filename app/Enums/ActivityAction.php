<?php

namespace App\Enums;

enum ActivityAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case StatusChanged = 'status_changed';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    public static function modules(): array
    {
        return ['product', 'coupon', 'review', 'order'];
    }
}
