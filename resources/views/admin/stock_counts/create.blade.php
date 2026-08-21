@extends('admin.layouts.app')

@section('page-title', __('global.sc_new_count'))

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.stock-counts.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold flex items-center gap-1">
        <span>&larr;</span> {{ __('global.back_to_orders') }}
    </a>
</div>

<div class="max-w-xl mx-auto bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
    <h2 class="text-lg font-bold mb-4 pb-2 border-b dark:border-gray-700 text-gray-900 dark:text-white">{{ __('global.sc_start_new_count') }}</h2>

    <form method="POST" action="{{ route('admin.stock-counts.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">{{ __('global.admin_branch') }} *</label>
            <select name="branch_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 border p-2">
                <option value="">{{ __('global.sc_select_branch') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branch_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">{{ __('global.admin_notes') }}</label>
            <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 border p-2">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
            {{ __('global.sc_start_counting') }}
        </button>
    </form>
</div>
@endsection
