@extends('admin.layouts.app')
@section('page-title', __('global.admin_add_district'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.districts.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_governorate') }} <span class="text-red-500">*</span></label>
            <select name="governorate_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                <option value="">{{ __('global.admin_select') }}</option>
                @foreach($governorates as $gov)
                    <option value="{{ $gov->id }}" {{ old('governorate_id') == $gov->id ? 'selected' : '' }}>{{ $gov->name }}</option>
                @endforeach
            </select>
            @error('governorate_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_city') }} <span class="text-red-500">*</span></label>
            <select name="city_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                <option value="">{{ __('global.admin_select') }}</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            @error('city_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_name') }} <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_type') }}</label>
            <input type="text" name="type" value="{{ old('type') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border" placeholder="{{ __('global.admin_district_type_placeholder') }}">
        </div>

        <div class="mb-6 flex items-center">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <label for="is_active" class="mr-2 block text-sm">{{ __('global.active') }}</label>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.save') }}</button>
            <a href="{{ route('admin.districts.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">{{ __('global.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
