@extends('layouts.store')

@section('content')
<div class="bg-gray-50 dark:bg-gray-800/50 py-6 border-b border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold">{{ __('return.request_exchange') }} #{{ $order->id }}</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <form action="{{ route('exchanges.store', $order) }}" method="POST">
        @csrf
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="font-bold mb-4">{{ __('return.select_items_to_exchange') }}</h2>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        @php $variants = $item->variant->product->variants; @endphp
                        <div class="p-4 border dark:border-gray-700 rounded-xl">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}"
                                       class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 exchange-checkbox"
                                       data-index="{{ $loop->index }}">
                                <div>
                                    <p class="font-semibold text-sm">{{ $item->product_name }}</p>
                                    <p class="text-xs text-gray-500">{{ __('global.qty_label_alt') }} {{ $item->quantity }}
                                        @if($item->color) | {{ $item->color }} @endif
                                        @if($item->size) | {{ $item->size }} @endif
                                    </p>
                                </div>
                            </label>
                            <div class="mt-3 exchange-variant hidden" data-index="{{ $loop->index }}">
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('return.choose_new_variant') }}</label>
                                <select name="items[{{ $loop->index }}][new_variant_id]" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm p-2 border focus:ring-indigo-500">
                                    <option value="">{{ __('return.select_variant') }}</option>
                                    @foreach($variants as $v)
                                        <option value="{{ $v->id }}" {{ $v->id === $item->product_variant_id ? 'disabled' : '' }}>
                                            {{ $v->name }}
                                            @if($v->color) ({{ $v->color }}) @endif
                                            @if($v->size) | {{ $v->size }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <label class="block font-bold mb-2">{{ __('return.reason_label') }}</label>
                <textarea name="reason" rows="3" required minlength="10"
                          placeholder="{{ __('return.exchange_reason_placeholder') }}"
                          class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <button type="submit" id="submitBtn" onclick="this.disabled=true;this.form.submit();" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition text-sm shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                {{ __('return.submit_exchange_request') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.exchange-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const idx = this.dataset.index;
            const variantDiv = document.querySelector(`.exchange-variant[data-index="${idx}"]`);
            if (variantDiv) {
                variantDiv.classList.toggle('hidden', !this.checked);
            }
        });
    });
</script>
@endpush
@endsection
