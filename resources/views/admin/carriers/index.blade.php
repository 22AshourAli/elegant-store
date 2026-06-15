@extends('admin.layouts.app')
@section('page-title', __('global.admin_carriers'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_carriers') }}</h2>
    <a href="{{ route('admin.carriers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_new') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_name') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_name_ar') }}</th>
                <th class="p-3 text-right">{{ __('global.carrier_code') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($carriers as $carrier)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-3 font-semibold">{{ $carrier->name }}</td>
                <td class="p-3">{{ $carrier->name_ar ?? '-' }}</td>
                <td class="p-3"><code class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-xs">{{ $carrier->code }}</code></td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs {{ $carrier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $carrier->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.carriers.edit', $carrier) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_edit') }}</a>
                    <form action="{{ route('admin.carriers.destroy', $carrier) }}" method="POST" class="inline" onsubmit="return confirm('{{ __("global.confirm_delete_carrier") }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-gray-500">{{ __('global.admin_no_carriers') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($carriers, 'links'))
<div class="mt-4">{{ $carriers->links() }}</div>
@endif
@endsection
