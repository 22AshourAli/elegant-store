@extends('admin.layouts.app')
@section('page-title', __('global.admin_edit_carrier'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.carriers.update', $carrier) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_name') }} <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $carrier->name) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_name_ar') }}</label>
            <input type="text" name="name_ar" value="{{ old('name_ar', $carrier->name_ar) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.carrier_code') }} <span class="text-red-500">*</span></label>
            <input type="text" name="code" required value="{{ old('code', $carrier->code) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('global.carrier_code_hint') }}</p>
            @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.carrier_api_key') }}</label>
            <input type="password" name="api_key" value="{{ old('api_key', $carrier->api_key) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('global.carrier_api_key_hint') }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.carrier_base_url') }}</label>
            <input type="url" name="base_url" value="{{ old('base_url', $carrier->base_url) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
        </div>
        <div class="mb-6 flex items-center">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $carrier->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <label for="is_active" class="mr-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('global.active') }}</label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.save') }}</button>
            <a href="{{ route('admin.carriers.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">{{ __('global.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
