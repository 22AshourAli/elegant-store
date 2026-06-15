@extends('admin.layouts.app')
@section('page-title', __('global.admin_manage_products'))
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">{{ __('global.admin_products') }}</h2>
    <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_add_new') }}</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <th class="p-3 text-right hidden md:table-cell">صورة</th>
                <th class="p-3 text-right">{{ __('global.admin_name') }}</th>
                <th class="p-3 text-right hidden md:table-cell">{{ __('global.admin_categories') }}</th>
                <th class="p-3 text-right">{{ __('global.original_price') }}</th>
                <th class="p-3 text-right hidden md:table-cell">{{ __('global.admin_has_variants') }}</th>
                <th class="p-3 text-right">{{ __('global.admin_status') }}</th>
                <th class="p-3 text-center">{{ __('global.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 even:bg-gray-50/50 dark:even:bg-gray-700/20">
                <td class="p-3 hidden md:table-cell">
                    @php $adminImg = $product->firstImageUrl(); @endphp
                    @if($adminImg !== asset('images/logo.svg'))
                        <img src="{{ $adminImg }}" loading="lazy" class="w-12 h-12 object-cover rounded shadow-sm">
                    @else
                        <span class="text-xs text-gray-400">{{ __('global.admin_no_image') }}</span>
                    @endif
                </td>
                <td class="p-3 font-semibold">{{ $product->name }}</td>
                <td class="p-3 hidden md:table-cell">{{ $product->category->name ?? '-' }}</td>
                <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400">{{ $product->base_price }}</td>
                <td class="p-3 hidden md:table-cell">
                    @if($product->has_variants)
                        <span class="text-blue-600 text-xs bg-blue-50 px-2 py-1 rounded">{{ __('global.yes') }}</span>
                    @else
                        <span class="text-gray-500 text-xs bg-gray-100 px-2 py-1 rounded">{{ __('global.no') }}</span>
                    @endif
                </td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-xs {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $product->is_active ? __('global.active') : __('global.inactive') }}
                    </span>
                </td>
                <td class="p-3 text-center">
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 dark:text-blue-400 hover:underline mx-1">{{ __('global.admin_edit') }}</a>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline mx-1" onclick="return confirm('{{ __("global.confirm_delete") }}')">{{ __('global.admin_delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-4 text-center text-gray-500">{{ __('global.admin_no_products') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $products->onEachSide(1)->links('vendor.pagination.admin') }}
</div>
@endsection
