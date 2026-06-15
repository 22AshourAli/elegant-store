@extends('admin.layouts.app')
@section('page-title', __('global.admin_shipping_settings'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.shipping-settings.update') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.fuel_surcharge_percentage') }}</label>
            <input type="number" step="0.01" min="0" max="100" name="fuel_surcharge_percentage" required value="{{ old('fuel_surcharge_percentage', \App\Models\Setting::getValue('fuel_surcharge_percentage', '0')) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            @error('fuel_surcharge_percentage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.free_shipping_threshold') }}</label>
            <input type="number" step="0.01" min="0" name="free_shipping_threshold" required value="{{ old('free_shipping_threshold', \App\Models\Setting::getValue('free_shipping_threshold', '500')) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            @error('free_shipping_threshold') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">{{ __('global.default_shipping_cost') }}</label>
            <input type="number" step="0.01" min="0" name="default_shipping_cost" required value="{{ old('default_shipping_cost', \App\Models\Setting::getValue('default_shipping_cost', '30')) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            @error('default_shipping_cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.save') }}</button>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">{{ __('global.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
