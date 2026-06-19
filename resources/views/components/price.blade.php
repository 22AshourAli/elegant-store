@props(['amount' => 0, 'bold' => false, 'color' => null])

@php
    $rounded = (int) round((float) $amount);
    $locale = app()->getLocale();
    $displayNumber = $locale === 'ar'
        ? str_replace(['0','1','2','3','4','5','6','7','8','9'], ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], (string)$rounded)
        : $rounded;
    $colorClass = match ($color) {
        'sale' => 'text-emerald-600 dark:text-emerald-400',
        'muted' => 'text-gray-400 line-through',
        'danger' => 'text-red-600 dark:text-red-400',
        default => 'text-gray-900 dark:text-white',
    };
    $weight = $bold ? 'font-extrabold' : 'font-semibold';
@endphp

<span class="{{ $weight }} {{ $colorClass }}">{{ $displayNumber }} {{ __('global.currency') }}</span>
