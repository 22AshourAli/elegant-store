@extends('admin.layouts.app')
@section('page-title', __('global.admin_cities'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_cities') }}</h2>
    <a href="{{ route('admin.cities.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_new') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow">
    <table class="w-full text-sm">
        <thead class="hidden md:table-header-group">
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_name') }}</th>
                <th class="p-3 text-right">{{ __('global.governorate') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cities as $city)
            <tr class="block md:table-row border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 p-3 md:p-0">
                <td class="block md:table-cell p-1 md:p-3"><span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.admin_name') }}</span><span class="font-semibold">{{ $city->name }}</span></td>
                <td class="block md:table-cell p-1 md:p-3"><span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.governorate') }}</span>{{ $city->governorate->name ?? '-' }}</td>
                <td class="block md:table-cell p-1 md:p-3"><span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.admin_status') }}</span>
                    <span class="px-2 py-1 rounded text-xs {{ $city->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $city->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="block md:table-cell p-1 md:p-3 text-right md:text-center"><span class="md:hidden font-bold text-xs text-gray-500 dark:text-gray-400 block">{{ __('global.admin_actions') }}</span>
                    <a href="{{ route('admin.cities.edit', $city) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1 text-xs sm:text-sm">{{ __('global.admin_edit') }}</a>
                    <a href="{{ route('admin.cities.toggle-active', $city) }}" class="text-amber-600 dark:text-amber-400 hover:underline mx-1 text-xs sm:text-sm" onclick="return confirm('{{ __("global.confirm_toggle") }}')">
                        {{ $city->is_active ? __('global.deactivate') : __('global.activate') }}
                    </a>
                    <form action="{{ route('admin.cities.destroy', $city) }}" method="POST" class="inline" onsubmit="return confirm('{{ __("global.confirm_delete_city") }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1 text-xs sm:text-sm">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="p-4 text-center text-gray-500">{{ __('global.admin_no_cities') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($cities, 'links'))
<div class="mt-4">{{ $cities->links('vendor.pagination.admin') }}</div>
@endif
@endsection
