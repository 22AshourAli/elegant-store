@extends('admin.layouts.app')
@section('page-title', __('global.admin_create_coupon'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.coupons.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_discount_code') }} <span class="text-red-500">*</span></label>
            <input type="text" name="code" required value="{{ old('code') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border font-mono">
            @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_discount_type') }} <span class="text-red-500">*</span></label>
                <select name="type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                    <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>{{ __('global.admin_percentage') }}</option>
                    <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>{{ __('global.admin_fixed_amount') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_discount_value') }} <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="value" required value="{{ old('value') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_min_order_amount') }}</label>
            <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_start_date') }}</label>
                <input type="datetime-local" name="valid_from" value="{{ old('valid_from') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_end_date') }}</label>
                <input type="datetime-local" name="valid_until" value="{{ old('valid_until') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_usage_limit') }}</label>
            <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" placeholder="{{ __('global.admin_usage_limit_placeholder') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
        </div>

        <div class="mb-6 flex items-center">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <label for="is_active" class="ml-2 mr-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('global.admin_coupon_active') }}</label>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.save') }}</button>
            <a href="{{ route('admin.coupons.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">{{ __('global.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
