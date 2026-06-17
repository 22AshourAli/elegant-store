@props([
    'nextCursor' => null,
    'prevCursor' => null,
    'hasMore' => false,
])

@if($prevCursor || $hasMore)
    <div class="flex items-center justify-between mt-4">
        <div class="flex gap-2">
            @if($prevCursor)
                <a href="{{ request()->fullUrlWithQuery(['cursor' => $prevCursor, 'dir' => 'prev']) }}" 
                   class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                   ← السابق
                </a>
            @endif
            @if($hasMore && $nextCursor)
                <a href="{{ request()->fullUrlWithQuery(['cursor' => $nextCursor]) }}"
                   class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                   التالي →
                </a>
            @endif
        </div>
    </div>
@endif
