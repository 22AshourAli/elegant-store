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
                        <p class="text-xs text-gray-500 mt-1">{{ __('global.admin_sale_price_info') }}</p>
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
                            <input type="text" name="colors" x-model="colors" @input="initColorImages" placeholder="{{ __('global.admin_colors_placeholder') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('global.admin_sizes_available') }}</label>
                            <input type="text" name="sizes" x-model="sizes" placeholder="S, M, L, XL" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mb-4">{{ __('global.admin_variants_price_info') }} <strong>{{ (int) round(old('base_price', 0)) }} {{ __('global.currency') }}</strong></p>

                    <!-- Color Image URLs -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border dark:border-gray-600">
                        <p class="text-sm font-semibold mb-3">{{ __('global.admin_color_image_urls') }}</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <template x-for="(color, ci) in colorList" :key="color">
                                <div>
                                    <label class="block text-xs font-medium mb-1" x-text="'رابط صورة ' + color"></label>
                                    <input type="url" :name="'color_image_urls['+color+']'" :placeholder="'https://example.com/'+color+'.jpg'" class="w-full text-xs border border-gray-300 dark:border-gray-600 p-1.5 rounded bg-white dark:bg-gray-800">
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border dark:border-gray-600">
                        <p class="text-sm font-semibold mb-3">اختر الفروع لتسجيل المخزون</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <template x-for="branch in branches" :key="branch.id">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" :value="String(branch.id)" x-model="selectedBranchIds" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span x-text="branch.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="p-2">{{ __('global.admin_enabled') }}</th>
                                    <th class="p-2">{{ __('global.color') }}</th>
                                    <th class="p-2">{{ __('global.size') }}</th>
                                    <th class="p-2">SKU</th>
                                    <th class="p-2">سعر مخصص</th>
                                    <template x-for="branch in selectedBranches" :key="branch.id">
                                        <th class="p-2" x-text="'{{ __('global.admin_stock') }} (' + branch.name + ')' "></th>
                                    </template>
                                    <th class="p-2">تكلفة الشراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(color, ci) in colorList" :key="color">
                                    <template x-for="(size, si) in sizeList" :key="size" x-init="ensureVariant(color + '_' + size)">
                                            <tr class="border-t dark:border-gray-700">
                                                <td class="p-2">
                                                    <input type="checkbox" :name="'variants['+color+'_'+size+']'" value="1" checked class="rounded" x-model="variantSelections[color+'_'+size]">
                                                </td>
                                                <td class="p-2 font-bold" x-text="color"></td>
                                                <td class="p-2 font-bold" x-text="size"></td>
                                                <td class="p-2">
                                                    <input type="text" :name="'variants_data['+color+'_'+size+'][sku]'" placeholder="SKU" class="w-28 px-2 py-1 text-sm border rounded dark:bg-gray-800 dark:border-gray-600" x-model="variantsData[color+'_'+size].sku">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" :name="'variants_data['+color+'_'+size+'][price_override]'" placeholder="سعر مخصص" class="w-28 px-2 py-1 text-sm border rounded dark:bg-gray-800 dark:border-gray-600" x-model.number="variantsData[color+'_'+size].price_override">
                                                </td>
                                                <template x-for="branch in selectedBranches" :key="branch.id">
                                                    <td class="p-2">
                                                        <input type="number" :name="'variant_stocks['+color+'_'+size+']['+branch.id+']'" placeholder="0" min="0" class="w-20 px-2 py-1 text-sm border rounded dark:bg-gray-800 dark:border-gray-600" x-model.number="variantsData[color+'_'+size].stocks[branch.id]">
                                                    </td>
                                                </template>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" :name="'cost_prices['+color+'_'+size+']'" placeholder="0" class="w-20 px-2 py-1 text-sm border rounded dark:bg-gray-800 dark:border-gray-600" x-model.number="variantsData[color+'_'+size].cost_price">
                                                </td>
                                            </tr>
                                        </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                                        <!-- Live Preview -->
                                        <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded shadow">
                                            <h4 class="font-semibold mb-3">معاينة المتغيرات (قبل الحفظ)</h4>
                                            <template x-if="Object.keys(variantsPreview).length">
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-sm text-right">
                                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                                            <tr>
                                                                <th class="p-2">اللون</th>
                                                                <th class="p-2">المقاس</th>
                                                                <th class="p-2">SKU</th>
                                                                <th class="p-2">سعر مخصص</th>
                                                                <th class="p-2">مجموع المخزون</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <template x-for="v in variantsPreview" :key="v.key">
                                                                <tr class="border-t dark:border-gray-700">
                                                                    <td class="p-2" x-text="v.color"></td>
                                                                    <td class="p-2" x-text="v.size"></td>
                                                                    <td class="p-2" x-text="v.sku || '-' "></td>
                                                                    <td class="p-2" x-text="v.price_override || '-' "></td>
                                                                    <td class="p-2" x-text="v.totalStock"></td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </template>
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
                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">تكلفة الشراء للوحدة ({{ __('global.currency') }})</label>
                        <input type="number" step="0.01" name="cost_prices[default]" placeholder="مثلاً 150" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border">
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
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">{{ __('global.upload_images') }}</label>
                    <input type="file" name="images[]" multiple accept=".png,.jpg,.jpeg,.webp,image/*" class="w-full border border-gray-300 dark:border-gray-600 p-2 rounded">
                    <p class="text-xs text-gray-500 mt-1">{{ __('global.admin_images_info') }}</p>
                </div>
                <div class="border-t pt-4 dark:border-gray-700">
                    <label class="block text-sm font-medium mb-1">{{ __('global.image_urls') }}</label>
                    <textarea name="image_urls" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2 border" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg">{{ old('image_urls') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ __('global.image_urls_info') }}</p>
                </div>
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
            branches: @json($branches->map(fn($b) => ['id' => (string)$b->id, 'name' => $b->name])),
            selectedBranchIds: @json($branches->pluck('id')->map(fn($id) => (string)$id)->toArray()),
            variantsData: {},
            variantSelections: {},

            get colorList() {
                return this.colors.split(',').map(c => c.trim()).filter(c => c);
            },
            get sizeList() {
                return this.sizes.split(',').map(s => s.trim()).filter(s => s);
            },
            get selectedBranches() {
                return this.branches.filter(branch => this.selectedBranchIds.includes(branch.id));
            },

            init() {
                this.initAllVariants();
                this.$watch('colors', () => this.initAllVariants());
                this.$watch('sizes', () => this.initAllVariants());
            },

            initAllVariants() {
                this.colorList.forEach(color => {
                    this.sizeList.forEach(size => {
                        const key = color + '_' + size;
                        this.ensureVariant(key, color, size);
                    });
                });
            },

            ensureVariant(key, color = null, size = null) {
                if (!this.variantsData[key]) {
                    const parts = key.split('_');
                    color = color || parts[0];
                    size = size || parts.slice(1).join('_');
                    this.variantsData[key] = {
                        sku: '',
                        price_override: '',
                        cost_price: '',
                        stocks: {},
                        color,
                        size,
                    };
                    this.branches.forEach(b => { this.variantsData[key].stocks[b.id] = 0; });
                    this.variantSelections[key] = true;
                }
            },

            get variantsPreview() {
                return Object.keys(this.variantsData)
                    .filter(k => this.variantSelections[k])
                    .map(k => {
                        const v = this.variantsData[k];
                        const totalStock = Object.values(v.stocks || {}).reduce((a, b) => a + Number(b || 0), 0);
                        return { ...v, key: k, totalStock };
                    });
            },

            initColorImages() {
                // placeholder to trigger reactivity for color images
            }
        }
    }
</script>
@endsection
