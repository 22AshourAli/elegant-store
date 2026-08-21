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
                    <span class="font-bold text-gray-900 dark:text-white">{{ $countedItems }} / {{ $totalItems }}</span>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm text-center">
                <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">{{ $totalSystem }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('global.sc_system_stock') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm text-center">
                <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $totalCounted }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('global.sc_physical_stock') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm text-center col-span-2">
                <p class="text-2xl font-extrabold {{ $totalDiff >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $totalDiff >= 0 ? '+' : '' }}{{ $totalDiff }}
                </p>
                <p class="text-xs text-gray-500 mt-1">{{ __('global.sc_total_difference') }}</p>
            </div>
        </div>

        <!-- Actions -->
        @if($stockCount->status === 'in_progress')
        <div class="space-y-2">
            <form method="POST" action="{{ route('admin.stock-counts.complete', $stockCount) }}" onsubmit="return confirm('{{ __('global.sc_confirm_complete') }}')">
                @csrf
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
                    {{ __('global.sc_finalize_count') }}
                </button>
            </form>
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
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('global.sc_counted_items') }} ({{ $countedItems }}/{{ $totalItems }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600 text-xs font-semibold text-gray-500 dark:text-gray-300">
                        <th class="p-3">{{ __('global.admin_products') }}</th>
                        <th class="p-3">{{ __('global.sc_sku') }}</th>
                        <th class="p-3 text-center">{{ __('global.sc_system') }}</th>
                        <th class="p-3 text-center">{{ __('global.sc_counted') }}</th>
                        <th class="p-3 text-center">{{ __('global.sc_diff') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($stockCount->items as $item)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 text-sm transition-colors" x-data="stockItem({{ $item->id }}, {{ $item->system_stock }}, {{ $item->counted_stock ?? 'null' }})">
                        <td class="p-3">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $item->variant->product->name ?? '—' }}</span>
                            @if($item->variant->color)
                                <span class="text-xs text-gray-500">{{ $item->variant->color }}</span>
                            @endif
                            @if($item->variant->size)
                                <span class="text-xs text-gray-500">— {{ $item->variant->size }}</span>
                            @endif
                        </td>
                        <td class="p-3 font-mono text-xs text-gray-500">{{ $item->variant->sku ?? '—' }}</td>
                        <td class="p-3 text-center font-bold text-gray-900 dark:text-white">{{ $item->system_stock }}</td>
                        <td class="p-3 text-center">
                            @if($stockCount->status === 'in_progress')
                            <div class="flex items-center justify-center gap-1">
                                <input type="number" min="0"
                                       x-model.number="counted"
                                       @change="save()"
                                       class="w-20 text-center rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 border p-1.5"
                                       placeholder="{{ $item->system_stock }}">
                                <button @click="save()" x-show="!saved" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400" title="Save">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <span x-show="saved" class="text-green-500">
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
                                <span class="text-gray-400">—</span>
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
function stockItem(itemId, systemStock, initialCounted) {
    return {
        counted: initialCounted,
        saved: initialCounted !== null,
        async save() {
            if (this.counted === null || this.counted === '') return;
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
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __("global.sc_item_updated") }}', type: 'success' } }));
            } catch(e) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: '{{ __("global.error_occurred") }}', type: 'error' } }));
            }
        }
    };
}
</script>
@endif
@endsection
