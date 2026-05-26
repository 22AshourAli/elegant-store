@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'mt-2 rounded-2xl border border-rose-200 bg-rose-50 dark:border-rose-900/70 dark:bg-rose-950/20 text-rose-700 dark:text-rose-200 px-3 py-3 text-sm space-y-1']) }} role="alert">
        <div class="flex items-start gap-2">
            <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-600 dark:text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                @foreach ((array) $messages as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        </div>
    </div>
@endif
