@extends('admin.layouts.app')
@section('page-title', __('global.admin_edit_product') . ': ' . $product->name)
@section('content')
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2 dark:border-gray-700">{{ __('global.admin_basic_info') }}</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">{{ __('global.admin_name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $product->name) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">{{ __('global.description') }}</label>
                    <textarea name="description" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_base_price') }} <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="base_price" required value="{{ old('base_price', $product->base_price) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_sale_price') }}</label>
                        <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2 dark:border-gray-700">{{ __('global.admin_edit_variants_stock') }}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="p-2">{{ __('global.admin_type') }}</th>
                                <th class="p-2">{{ __('global.admin_variant_image') }}</th>
                                <th class="p-2">{{ __('global.admin_sku') }}</th>
                                <th class="p-2">{{ __('global.admin_custom_price') }} ({{ __('global.currency') }})</th>
                                <th class="p-2">{{ __('global.admin_sale_price') }} ({{ __('global.currency') }})</th>
                                <th class="p-2">{{ __('global.admin_stock_branches') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variants as $variant)
                            <tr class="border-t dark:border-gray-700">
                                <td class="p-2 font-bold">{{ $variant->is_default ? __('global.admin_default_variant') : $variant->color . ' - ' . $variant->size }}</td>
                                <td class="p-2">
                                    <div class="space-y-1">
                                        <input type="file" name="variants[{{ $variant->id }}][image]" accept=".png,.jpg,.jpeg,.webp,image/*" class="w-full text-xs">
                                        @if($variant->hasMedia('variant_images'))
                                            <div class="mt-1 flex items-center gap-2">
                                                <img src="{{ $variant->getFirstMediaUrl('variant_images') }}" class="w-10 h-10 object-cover rounded border">
                                                <label class="flex items-center text-xs text-red-500 cursor-pointer">
                                                    <input type="checkbox" name="variants[{{ $variant->id }}][delete_image]" value="1" class="rounded text-red-600 ml-1">
                                                    <span>{{ __('global.admin_delete') }}</span>
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-2">
                                    <input type="text" name="variants[{{ $variant->id }}][sku]" value="{{ old('variants.'.$variant->id.'.sku', $variant->sku) }}" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-2 py-1 text-xs">
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" name="variants[{{ $variant->id }}][price_override]" value="{{ old('variants.'.$variant->id.'.price_override', $variant->price_override) }}" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-2 py-1 text-xs" placeholder="{{ __('global.admin_default') }}">
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" name="variants[{{ $variant->id }}][sale_price]" value="{{ old('variants.'.$variant->id.'.sale_price', $variant->sale_price) }}" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-2 py-1 text-xs" placeholder="{{ __('global.admin_no_sale') }}">
                                </td>
                                <td class="p-2">
                                    <div class="space-y-1">
                                        @foreach($branches as $branch)
                                            @php
                                                $stock = $variant->branches->firstWhere('id', $branch->id)?->pivot?->stock ?? 0;
                                            @endphp
                                            <div class="flex items-center justify-between gap-2 text-xs">
                                                <span class="text-gray-500 dark:text-gray-400 font-semibold">{{ $branch->name }}:</span>
                                                <input type="number" name="variants[{{ $variant->id }}][stocks][{{ $branch->id }}]" value="{{ old('variants.'.$variant->id.'.stocks.'.$branch->id, $stock) }}" min="0" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-1.5 py-0.5 text-xs w-16 text-center">
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2 dark:border-gray-700">{{ __('global.admin_category_status') }}</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">{{ __('global.admin_categories') }} <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                        <option value="">-- {{ __('global.admin_select_category') }} --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3 mt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="rounded text-indigo-600 ml-2">
                        <span>{{ __('global.admin_active_product') }}</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="featured" value="1" {{ $product->featured ? 'checked' : '' }} class="rounded text-indigo-600 ml-2">
                        <span>{{ __('global.admin_featured_product') }}</span>
                    </label>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2 dark:border-gray-700">{{ __('global.admin_product_images') }}</h3>
                <label class="block text-sm font-medium mb-2">{{ __('global.admin_add_new_images') }}:</label>
                <input type="file" name="images[]" multiple accept=".png,.jpg,.jpeg,.webp,image/*" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded mb-4">

                @if($product->getMedia('product_images')->count() > 0)
                <div class="mt-4 border-t pt-4 dark:border-gray-700">
                    <p class="text-sm font-semibold mb-2">{{ __('global.admin_current_images') }}:</p>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($product->getMedia('product_images') as $media)
                            <div class="relative group border rounded-lg overflow-hidden dark:border-gray-700">
                                <img src="{{ $media->getUrl('thumb') }}" class="w-full h-16 object-cover">
                                <label class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white cursor-pointer text-xs transition-opacity duration-200">
                                    <input type="checkbox" name="delete_images[]" value="{{ $media->id }}" class="rounded text-red-600 ml-1">
                                    <span>{{ __('global.admin_delete') }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow p-6 flex flex-col gap-3">
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-md hover:bg-indigo-700 transition">{{ __('global.admin_update_product') }}</button>
                <a href="{{ route('admin.products.index') }}" class="w-full text-center bg-gray-200 text-gray-800 font-bold py-3 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 transition">{{ __('global.admin_cancel') }}</a>
            </div>
        </div>
    </div>
</form>
@endsection
