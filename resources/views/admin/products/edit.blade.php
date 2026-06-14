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

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_discount_start') }}</label>
                        <input type="datetime-local" name="discount_start" value="{{ old('discount_start', $product->discount_start?->format('Y-m-d\TH:i')) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_discount_end') }}</label>
                        <input type="datetime-local" name="discount_end" value="{{ old('discount_end', $product->discount_end?->format('Y-m-d\TH:i')) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
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
                                <th class="p-2">تكلفة الشراء ({{ __('global.currency') }})</th>
                                <th class="p-2">{{ __('global.admin_stock_branches') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variants as $variant)
                            <tr class="border-t dark:border-gray-700">
                                <td class="p-2">
                                    @if($variant->is_default)
                                        <span class="font-bold">{{ __('global.admin_default_variant') }}</span>
                                    @else
                                        <input type="text" name="variants[{{ $variant->id }}][color]" value="{{ old('variants.'.$variant->id.'.color', $variant->color) }}" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-2 py-1 text-xs mb-1" placeholder="اللون">
                                        <input type="text" name="variants[{{ $variant->id }}][size]" value="{{ old('variants.'.$variant->id.'.size', $variant->size) }}" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-2 py-1 text-xs" placeholder="المقاس">
                                    @endif
                                </td>
                                <td class="p-2">
                                    <div class="space-y-1">
                                        <input type="url" name="variants[{{ $variant->id }}][image_url]" value="{{ old('variants.'.$variant->id.'.image_url', $variant->image_url) }}" placeholder="https://example.com/image.jpg" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-2 py-1 text-xs">
                                        @if($variant->image_url)
                                            <img src="{{ $variant->image_url }}" class="w-10 h-10 object-cover rounded border">
                                        @elseif($variant->hasMedia('variant_images'))
                                            <img src="{{ $variant->getFirstMediaUrl('variant_images') }}" class="w-10 h-10 object-cover rounded border">
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
                                    <input type="number" step="0.01" name="variants[{{ $variant->id }}][cost_price]" value="{{ old('variants.'.$variant->id.'.cost_price', $variant->cost_price) }}" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-2 py-1 text-xs" placeholder="مثلاً 150">
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

                <div class="mt-6 p-4 border-t dark:border-gray-700">
                    <h4 class="text-md font-bold mb-3">إضافة متغيرات جديدة</h4>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.admin_colors_available') }}</label>
                            <input type="text" name="new_colors" placeholder="{{ __('global.admin_colors_placeholder') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.admin_sizes_available') }}</label>
                            <input type="text" name="new_sizes" placeholder="S, M, L, XL" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                        </div>
                    </div>
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
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">{{ __('global.upload_images') }}</label>
                    <input type="file" name="images[]" multiple accept=".png,.jpg,.jpeg,.webp,image/*" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded">
                    <p class="text-xs text-gray-500 mt-1">{{ __('global.admin_images_info') }}</p>
                </div>

                <div class="border-t pt-4 mb-4 dark:border-gray-700">
                    <label class="block text-sm font-medium mb-1">{{ __('global.image_urls') }}</label>
                    <textarea name="image_urls" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg">{{ old('image_urls', is_array($product->image_urls) ? implode("\n", $product->image_urls) : '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ __('global.image_urls_info') }}</p>
                </div>

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
