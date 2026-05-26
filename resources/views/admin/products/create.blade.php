@extends('admin.layouts.app')
@section('page-title', __('global.admin_add_new_product'))
@section('content')
<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" x-data="productForm()">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2 dark:border-gray-700">{{ __('global.admin_basic_info') }}</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">{{ __('global.admin_name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">{{ __('global.description') }}</label>
                    <textarea name="description" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_base_price') }} <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="base_price" required value="{{ old('base_price') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_sale_price') }}</label>
                        <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_discount_start') }}</label>
                        <input type="datetime-local" name="discount_start" value="{{ old('discount_start') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('global.admin_discount_end') }}</label>
                        <input type="datetime-local" name="discount_end" value="{{ old('discount_end') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2 dark:border-gray-700 flex justify-between">
                    {{ __('global.admin_variants_stock') }}
                    <label class="flex items-center text-sm font-normal cursor-pointer">
                        <input type="checkbox" name="has_variants" value="1" x-model="hasVariants" class="rounded text-indigo-600 ml-2">
                        {{ __('global.admin_has_sizes_colors') }}
                    </label>
                </h3>

                <div x-show="hasVariants" class="mt-4" style="display: none;">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.admin_colors_available') }}</label>
                            <input type="text" name="colors" x-model="colors" placeholder="{{ __('global.admin_colors_placeholder') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.admin_sizes_available') }}</label>
                            <input type="text" name="sizes" x-model="sizes" placeholder="S, M, L, XL" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="p-2">{{ __('global.admin_enabled') }}</th>
                                    <th class="p-2">{{ __('global.color') }}</th>
                                    <th class="p-2">{{ __('global.size') }}</th>
                                    <th class="p-2">{{ __('global.admin_custom_price') }}</th>
                                    @foreach($branches as $branch)
                                        <th class="p-2">{{ __('global.admin_stock') }} ({{ $branch->name }})</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="color in colorList" :key="color">
                                    <template x-for="size in sizeList" :key="size">
                                        <tr class="border-t dark:border-gray-700">
                                            <td class="p-2"><input type="checkbox" :name="'variants['+color+'_'+size+']'" value="1" checked class="rounded"></td>
                                            <td class="p-2 font-bold" x-text="color"></td>
                                            <td class="p-2 font-bold" x-text="size"></td>
                                            <td class="p-2">
                                                <input type="number" step="0.01" :name="'variant_prices['+color+'_'+size+']'" placeholder="{{ __('global.admin_default') }}" class="w-24 px-2 py-1 text-sm border rounded dark:bg-gray-800 dark:border-gray-600">
                                            </td>
                                            @foreach($branches as $branch)
                                            <td class="p-2">
                                                <input type="number" :name="'variant_stocks['+color+'_'+size+'][{{ $branch->id }}]'" placeholder="0" value="0" min="0" class="w-20 px-2 py-1 text-sm border rounded dark:bg-gray-800 dark:border-gray-600">
                                            </td>
                                            @endforeach
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="!hasVariants" class="mt-4">
                    <p class="text-sm text-gray-500 mb-4">{{ __('global.admin_no_variants_info') }}</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($branches as $branch)
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ $branch->name }}</label>
                            <input type="number" name="variant_stocks[default][{{ $branch->id }}]" placeholder="0" value="0" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                        </div>
                        @endforeach
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
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3 mt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded text-indigo-600 ml-2">
                        <span>{{ __('global.admin_active_product') }}</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="featured" value="1" class="rounded text-indigo-600 ml-2">
                        <span>{{ __('global.admin_featured_product') }}</span>
                    </label>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2 dark:border-gray-700">{{ __('global.admin_product_images') }}</h3>
                <input type="file" name="images[]" multiple accept=".png,.jpg,.jpeg,.webp,image/*" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded">
                <p class="text-xs text-gray-500 mt-2">{{ __('global.admin_images_info') }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded shadow p-6 flex flex-col gap-3">
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-md hover:bg-indigo-700 transition">{{ __('global.admin_save_product') }}</button>
                <a href="{{ route('admin.products.index') }}" class="w-full text-center bg-gray-200 text-gray-800 font-bold py-3 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 transition">{{ __('global.admin_cancel') }}</a>
            </div>
        </div>
    </div>
</form>

<script>
    function productForm() {
        return {
            hasVariants: false,
            colors: '',
            sizes: '',
            get colorList() {
                return this.colors.split(',').map(c => c.trim()).filter(c => c);
            },
            get sizeList() {
                return this.sizes.split(',').map(s => s.trim()).filter(s => s);
            }
        }
    }
</script>
@endsection
