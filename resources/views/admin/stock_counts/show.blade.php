@extends('admin.layouts.app')

@section('page-title', __('global.sc_count') . ' #' . $stockCount->id)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.stock-counts.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold flex items-center gap-1">
        <span>&larr;</span> {{ __('global.back_to_orders') }}
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-6 shadow-sm border border-green-200">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-6 shadow-sm border border-red-200">{{ session('error') }}</div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Summary Cards -->
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('global.sc_count_details') }}</h2>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                    @if($stockCount->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                    @elseif($stockCount->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @endif">
                    {{ __('global.sc_status_' . $stockCount->status) }}
                </span>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('global.sc_reference') }}</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $stockCount->reference_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('global.admin_branch') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $stockCount->branch->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('global.admin_date') }}</span>
                    <span class="text-gray-900 dark:text-white">{{ $stockCount->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @if($stockCount->completed_at)
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('global.sc_completed_at') }}</span>
                    <span class="text-gray-900 dark:text-white">{{ $stockCount->completed_at->format('Y-m-d H:i') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ __('global.sc_progress') }}</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $countedItems }} / {{ $totalItems }} {{ __('global.sc_items_lbl') }}</span>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">{{ __('global.sc_system_stock') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('global.sc_system_stock_hint') }}</p>
                    </div>
                    <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">{{ $totalSystem }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">{{ __('global.sc_physical_stock') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('global.sc_physical_stock_hint') }}</p>
                    </div>
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $totalCounted }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">{{ __('global.sc_total_difference') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('global.sc_total_difference_hint') }}</p>
                    </div>
                    <p class="text-2xl font-extrabold {{ $totalDiff > 0 ? 'text-green-600 dark:text-green-400' : ($totalDiff < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400') }}">
                        {{ $totalDiff > 0 ? '+' : '' }}{{ $totalDiff }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($stockCount->status === 'in_progress')
        <div class="space-y-2">
            <form method="POST" action="{{ route('admin.stock-counts.complete', $stockCount) }}" onsubmit="return confirm('{{ __('global.sc_confirm_complete') }}')">
                @csrf
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" {{ $countedItems < $totalItems ? 'disabled' : '' }}>
                    {{ __('global.sc_finalize_count') }}
                </button>
            </form>
            @if($countedItems < $totalItems)
                <p class="text-xs text-center text-gray-400">{{ __('global.sc_must_count_all') }}</p>
            @endif
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.stock-counts.cancel', $stockCount) }}" class="flex-1" onsubmit="return confirm('{{ __('global.admin_confirm_cancel_msg') }}')">
                    @csrf
                    <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 rounded-lg text-xs transition-colors">{{ __('global.sc_cancel_count') }}</button>
                </form>
                <form method="POST" action="{{ route('admin.stock-counts.destroy', $stockCount) }}" class="flex-1" onsubmit="return confirm('{{ __('global.admin_confirm_delete_msg') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg text-xs transition-colors">{{ __('global.admin_delete') }}</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <!-- Items Table -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden" x-data="stockCountSearch()">
        <div class="p-4 border-b dark:border-gray-700 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('global.sc_counted_items') }} ({{ $countedItems }}/{{ $totalItems }})</h2>
            </div>
            <div class="flex gap-2">
                <input type="text" x-model="search" placeholder="{{ __('global.sc_search_placeholder') }}"
                       class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 border px-3 py-2">
                <span class="flex items-center px-3 text-sm text-gray-400" x-text="filteredCount + ' / {{ $totalItems }}'"></span>
            </div>
        </div>
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-right border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600 text-xs font-semibold text-gray-500 dark:text-gray-300">
                        <th class="p-3 text-right">#</th>
                        <th class="p-3 text-right">{{ __('global.admin_products') }} / {{ __('global.sc_sku') }}</th>
                        <th class="p-3 text-center">
                            <div>{{ __('global.sc_system') }}</div>
                            <div class="font-normal text-[10px] text-gray-400">{{ __('global.sc_system_hint_short') }}</div>
                        </th>
                        <th class="p-3 text-center">
                            <div>{{ __('global.sc_counted') }}</div>
                            <div class="font-normal text-[10px] text-gray-400">{{ __('global.sc_counted_hint_short') }}</div>
                        </th>
                        <th class="p-3 text-center">{{ __('global.sc_diff') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($stockCount->items as $index => $item)
                    @php
                        $v = $item->variant;
                        $productName = $v?->product?->name ?? ($v?->sku ?? '#' . $item->product_variant_id);
                        $variantParts = array_filter([$v?->color, $v?->size]);
                        $variantLabel = $variantParts ? implode(' / ', $variantParts) : '';
                        $searchText = strtolower($productName . ' ' . ($v?->sku ?? '') . ' ' . $variantLabel);
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 text-sm transition-colors"
                        x-data="stockItem({{ $item->id }}, {{ $item->system_stock }}, {{ $item->counted_stock ?? 'null' }})"
                        x-show="search === '' || '{{ $searchText }}'.includes(search)"
                        x-transition>
                        <td class="p-3 text-xs text-gray-400">{{ $index + 1 }}</td>
                        <td class="p-3">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $productName }}</div>
                            @if($variantLabel || $v?->sku)
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($variantLabel)
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded">{{ $variantLabel }}</span>
                                @endif
                                @if($v?->sku)
                                    <span class="text-xs text-gray-400 font-mono">{{ $v->sku }}</span>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-bold text-sm">{{ $item->system_stock }}</span>
                        </td>
                        <td class="p-3 text-center">
                            @if($stockCount->status === 'in_progress')
                            <div class="flex items-center justify-center gap-1">
                                <input type="number" min="0"
                                       x-model.number="counted"
                                       @change="save()"
                                       @keydown.enter.prevent="save()"
                                       class="w-20 text-center rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 border p-1.5 {{ saved ? 'border-green-400 dark:border-green-600' : '' }}">
                                <span x-show="saving" class="text-gray-400">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                </span>
                                <span x-show="saved && !saving" class="text-green-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </div>
                            @else
                                <span class="font-bold {{ ($item->difference ?? 0) > 0 ? 'text-green-600' : (($item->difference ?? 0) < 0 ? 'text-red-600' : 'text-gray-900 dark:text-white') }}">
                                    {{ $item->counted_stock ?? '—' }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if(!is_null($item->difference))
                            <span class="font-bold px-2 py-0.5 rounded-full text-xs
                                {{ $item->difference > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : ($item->difference < 0 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400') }}">
                                {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                            </span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($stockCount->status === 'in_progress')
<script>
function stockCountSearch() {
    return {
        search: '',
        get filteredCount() {
            if (this.search === '') return {{ $totalItems }};
            return document.querySelectorAll('tbody tr[x-show]').length;
        }
    };
}

function stockItem(itemId, systemStock, initialCounted) {
    return {
        counted: initialCounted,
        saved: initialCounted !== null,
        saving: false,
        async save() {
            if (this.counted === null || this.counted === '' || this.counted === undefined) return;
            this.saving = true;
            this.saved = false;
            try {
                const res = await fetch('{{ route("admin.stock-counts.update-item", [$stockCount, "__ITEM_ID__"]) }}'.replace('__ITEM_ID__', itemId), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ counted_stock: this.counted })
                });
                if (!res.ok) throw new Error('Error');
                this.saved = true;
                this.saving = false;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __("global.sc_item_updated") }}', type: 'success' } }));
            } catch(e) {
                this.saving = false;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __("global.error_occurred") }}', type: 'error' } }));
            }
        }
    };
}
</script>
@endif
@endsection
