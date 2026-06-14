@extends('admin.layouts.app')
@section('page-title', __('global.admin_manage_shipping_providers'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_shipping_providers') }}</h2>
    <a href="{{ route('admin.shipping-providers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_new') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">#</th>
                <th class="p-3 text-right">{{ __('global.admin_name') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_type') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_phone') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_contact_person') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($providers as $provider)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-3">{{ $provider->id }}</td>
                <td class="p-3">{{ $provider->name }}</td>
                <td class="p-3">{{ $provider->type ?? '-' }}</td>
                <td class="p-3">{{ $provider->phone ?? '-' }}</td>
                <td class="p-3">{{ $provider->contact_person ?? '-' }}</td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs {{ $provider->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $provider->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.shipping-providers.edit', $provider) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_edit') }}</a>
                    <form action="{{ route('admin.shipping-providers.destroy', $provider) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1" onclick="return confirm('{{ __("global.confirm_delete") }}')">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-4 text-center text-gray-500">{{ __('global.admin_no_shipping_providers') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($providers, 'links'))
<div class="mt-4">{{ $providers->links() }}</div>
@endif
@endsection
