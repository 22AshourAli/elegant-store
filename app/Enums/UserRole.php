<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Manager = 'manager';
    case SuperAdmin = 'super_admin';

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    public static function adminRoles(): array
    {
        return [self::Manager, self::SuperAdmin];
    }
}
