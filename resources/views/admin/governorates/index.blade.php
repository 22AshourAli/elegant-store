@extends('admin.layouts.app')
@section('page-title', __('global.admin_governorates'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_governorates') }}</h2>
    <a href="{{ route('admin.governorates.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_new') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_name') }}</th>
                <th class="p-3 text-right hidden md:table-cell">{{ __('global.base_shipping_cost') }}</th>
                <th class="p-3 text-right hidden md:table-cell">{{ __('global.cities_count') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($governorates as $gov)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 even:bg-gray-50/50 dark:even:bg-gray-700/20">
                <td class="p-3 font-semibold">{{ $gov->name }}</td>
                <td class="p-3 hidden md:table-cell">{{ number_format($gov->base_shipping_cost, 2) }} {{ __('global.currency') }}</td>
                <td class="p-3 hidden md:table-cell">{{ $gov->cities_count }}</td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs {{ $gov->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $gov->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.governorates.edit', $gov) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_edit') }}</a>
                    <form action="{{ route('admin.governorates.toggle-active', $gov) }}" method="POST" class="inline" onsubmit="return confirm('{{ __("global.confirm_toggle") }}')">
                        @csrf
                        <button type="submit" class="text-amber-600 dark:text-amber-400 hover:underline mx-1 cursor-pointer">{{ $gov->is_active ? __('global.deactivate') : __('global.activate') }}</button>
                    </form>
                    <form action="{{ route('admin.governorates.destroy', $gov) }}" method="POST" class="inline" onsubmit="return confirm('{{ __("global.confirm_delete_governorate") }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1 cursor-pointer">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-gray-500">{{ __('global.admin_no_governorates') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($governorates, 'links'))
<div class="mt-4">{{ $governorates->onEachSide(1)->links('vendor.pagination.admin') }}</div>
@endif
@endsection
