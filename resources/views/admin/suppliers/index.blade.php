@extends('admin.layouts.app')
@section('page-title', __('global.admin_suppliers'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_suppliers') }}</h2>
    <a href="{{ route('admin.suppliers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_supplier') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right">{{ __('global.admin_name') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_phone') }}</th>
                <th class="p-3 text-right hidden md:table-cell">{{ __('global.admin_email') }}</th>
                <th class="p-3 text-right hidden md:table-cell">{{ __('global.admin_contact_person') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 even:bg-gray-50/50 dark:even:bg-gray-700/20">
                <td class="p-3">{{ $supplier->name }}</td>
                <td class="p-3">{{ $supplier->phone ?? '-' }}</td>
                <td class="p-3 hidden md:table-cell">{{ $supplier->email ?? '-' }}</td>
                <td class="p-3 hidden md:table-cell">{{ $supplier->contact_person ?? '-' }}</td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $supplier->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_edit') }}</a>
                    <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1" onclick="return confirm('{{ __("global.confirm_delete") }}')">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-4 text-center text-gray-500">{{ __('global.admin_no_suppliers') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if(method_exists($suppliers, 'links'))
<div class="mt-4">{{ $suppliers->onEachSide(1)->links('vendor.pagination.admin') }}</div>
@endif
@endsection
