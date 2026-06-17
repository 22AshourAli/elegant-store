@props(['type' => 'success', 'message' => null])

@php
    $message ??= session($type);
    $styles = [
        'success' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800',
        'error'   => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
        'warning' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800',
        'info'    => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
    ];
    $class = $styles[$type] ?? $styles['success'];
@endphp

@if($message)
    <div class="{{ $class }} p-4 rounded-xl mb-6 shadow-sm border" role="alert">
        {{ $message }}
    </div>
@endif
