<?php

namespace App\Helpers;

class Numbers
{
    protected static array $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    public static function toArabic(string $value): string
    {
        if (app()->getLocale() !== 'ar') {
            return $value;
        }
        return str_replace(range(0, 9), self::$arabicDigits, $value);
    }

    public static function format($number, ?int $decimals = null): string
    {
        if ($number === null || $number === '') return '—';
        $number = (float) $number;
        $decimals ??= (fmod($number, 1) == 0) ? 0 : 2;
        $formatted = number_format($number, $decimals, '.', ',');
        return self::toArabic($formatted);
    }

    public static function formatCurrency($number, string $currency = 'EGP'): string
    {
        return self::format($number, 2) . ' ' . $currency;
    }

    public static function formatPercent($number): string
    {
        return self::toArabic(number_format((float) $number, 1)) . '%';
    }

    public static function formatInteger($number): string
    {
        return self::toArabic(number_format((int) $number, 0, '.', ','));
    }
}
