@extends('admin.layouts.app')
@section('page-title', __('global.admin_add_supplier'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.suppliers.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_name') }} <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name') }}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_phone') }}</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_address') }}</label>
            <textarea name="address" rows="2" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">{{ old('address') }}</textarea>
            @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_contact_person') }}</label>
            <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
            @error('contact_person') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_contact_phone') }}</label>
            <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
            @error('contact_phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_notes') }}</label>
            <textarea name="notes" rows="3" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">{{ old('notes') }}</textarea>
            @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-6 flex items-center">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="is_active" class="mr-2 block text-sm">{{ __('global.active') }}</label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_save') }}</button>
            <a href="{{ route('admin.suppliers.index') }}" class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-500">{{ __('global.admin_cancel') }}</a>
        </div>
    </form>
</div>
@endsection
