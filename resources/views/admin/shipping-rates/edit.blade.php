@extends('admin.layouts.app')
@section('page-title', __('global.admin_edit_rate'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.shipping-rates.update', $shippingRate) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.governorate') }} <span class="text-red-500">*</span></label>
            <select name="governorate_id" id="governorate_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border"
                onchange="loadCities(this.value)">
                <option value="">{{ __('global.admin_select') }}</option>
                @foreach($governorates as $gov)
                    <option value="{{ $gov->id }}" {{ old('governorate_id', $shippingRate->governorate_id) == $gov->id ? 'selected' : '' }}>{{ $gov->name }}</option>
                @endforeach
            </select>
            @error('governorate_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.city') }}</label>
            <select name="city_id" id="city_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                <option value="">{{ __('global.all_cities') }}</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ old('city_id', $shippingRate->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            @error('city_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.rate') }} <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" name="rate" required value="{{ old('rate', $shippingRate->rate) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            @error('rate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.min_cart_amount') }}</label>
            <input type="number" step="0.01" min="0" name="min_cart_amount" value="{{ old('min_cart_amount', $shippingRate->min_cart_amount) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('global.min_cart_hint') }}</p>
            @error('min_cart_amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-6 flex items-center">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $shippingRate->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <label for="is_active" class="mr-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('global.active') }}</label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.save') }}</button>
            <a href="{{ route('admin.shipping-rates.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">{{ __('global.cancel') }}</a>
        </div>
    </form>
</div>

<script>
function loadCities(governorateId) {
    var citySelect = document.getElementById('city_id');
    citySelect.innerHTML = '<option value="">@lang('global.all_cities')</option>';
    if (!governorateId) return;
    fetch('{{ route('admin.shipping-rates.get-cities') }}?governorate_id=' + governorateId)
        .then(function(r) { return r.json(); })
        .then(function(cities) {
            cities.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                citySelect.appendChild(opt);
            });
        });
}
</script>
@endsection
