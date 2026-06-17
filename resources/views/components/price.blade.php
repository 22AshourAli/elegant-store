@props(['amount' => 0, 'bold' => false, 'color' => null])

@php
    $rounded = (int) round((float) $amount);
    $colorClass = match ($color) {
        'sale' => 'text-emerald-600 dark:text-emerald-400',
        'muted' => 'text-gray-400 line-through',
        'danger' => 'text-red-600 dark:text-red-400',
        default => 'text-gray-900 dark:text-white',
    };
    $weight = $bold ? 'font-extrabold' : 'font-semibold';
@endphp

<span class="{{ $weight }} {{ $colorClass }}">{{ $rounded }} {{ __('global.currency') }}</span>
