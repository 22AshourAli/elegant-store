<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category', 'media')->latest()->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.products.create', compact('categories', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'discount_start' => 'nullable|date',
            'discount_end' => 'nullable|date',
            'has_variants' => 'boolean',
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'colors' => 'nullable|string',
            'sizes' => 'nullable|string',
            'variants' => 'nullable|array',
            'variant_stocks' => 'nullable|array',
            'variant_stocks.*' => 'nullable|array',
            'variant_stocks.*.*' => 'nullable|integer|min:0',
            'cost_prices' => 'nullable|array',
            'cost_prices.*' => 'nullable|numeric|min:0',
            'variants_data' => 'nullable|array',
            'variants_data.*.sku' => 'nullable|string|max:255',
            'variants_data.*.price_override' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,webp,jpg|max:2048',
            'color_image_urls' => 'nullable|array',
            'color_image_urls.*' => 'nullable|string|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        $validated['has_variants'] = $request->boolean('has_variants');
        $validated['featured'] = $request->boolean('featured');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image_urls'] = $this->parseImageUrls($request->input('image_urls'));

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $product->addMedia($image)->toMediaCollection('product_images');
            }
        }

        if ($product->has_variants) {
            $colors = array_filter(array_map('trim', explode(',', $request->colors)));
            $sizes = array_filter(array_map('trim', explode(',', $request->sizes)));
            $variantData = $request->variants ?? [];

            foreach ($colors as $color) {
                foreach ($sizes as $size) {
                    $key = $color . '_' . $size;

                    if (empty($variantData) || isset($variantData[$key])) {
                        $cost = $validated['cost_prices'][$key] ?? null;
                        $vdata = $request->input('variants_data.' . $key, []);
                        $variant = $product->variants()->create([
                            'color' => $color,
                            'size' => $size,
                            'sku' => $vdata['sku'] ?? ($product->id . '-' . $color . '-' . $size),
                            'price_override' => $vdata['price_override'] ?? null,
                            'cost_price' => $cost !== null && $cost !== '' ? (float) $cost : null,
                        ]);

                        $stocks = $validated['variant_stocks'][$key] ?? [];
                        foreach ($stocks as $branchId => $qty) {
                            $variant->branches()->attach($branchId, ['stock' => max(0, (int)$qty)]);
                        }
                    }
                }
            }
        } else {
            $cost = $validated['cost_prices']['default'] ?? null;
            $variant = $product->variants()->create([
                'is_default' => true,
                'cost_price' => $cost !== null && $cost !== '' ? (float) $cost : null,
            ]);

            $stocks = $validated['variant_stocks']['default'] ?? [];
            foreach (Branch::all() as $branch) {
                $qty = $stocks[$branch->id] ?? 0;
                $variant->branches()->attach($branch->id, ['stock' => max(0, (int)$qty)]);
            }
        }

        // Attach color-specific image URLs to the first variant of each color
        if ($request->has('color_image_urls')) {
            foreach ($request->input('color_image_urls') as $color => $url) {
                if (!empty(trim($url))) {
                    $first = $product->variants()->where('color', $color)->first();
                    if ($first) {
                        $first->update(['image_url' => trim($url)]);
                    }
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $product->load('variants.branches', 'media');
        return view('admin.products.edit', compact('product', 'categories', 'branches'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'discount_start' => 'nullable|date',
            'discount_end' => 'nullable|date',
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.image_url' => 'nullable|string|max:2048',
            'variants.*.stocks' => 'nullable|array',
            'variants.*.stocks.*' => 'nullable|integer|min:0',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        $validated['featured'] = $request->boolean('featured');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image_urls'] = $this->parseImageUrls($request->input('image_urls'));

        $product->update($validated);

        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images') as $mediaId) {
                $media = $product->media()->find($mediaId);
                if ($media) {
                    $media->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $product->addMedia($image)->toMediaCollection('product_images');
            }
        }

        if ($request->has('variants')) {
            foreach ($request->input('variants') as $variantId => $vData) {
                $variant = $product->variants()->findOrFail($variantId);
                $variant->update([
                    'price_override' => empty($vData['price_override']) ? null : $vData['price_override'],
                    'cost_price' => array_key_exists('cost_price', $vData) && $vData['cost_price'] !== '' && $vData['cost_price'] !== null ? (float) $vData['cost_price'] : null,
                    'sku' => $vData['sku'] ?? null,
                    'image_url' => !empty($vData['image_url']) ? trim($vData['image_url']) : null,
                ]);

                if (isset($vData['stocks'])) {
                    foreach ($vData['stocks'] as $branchId => $qty) {
                        $variant->branches()->syncWithoutDetaching([
                            $branchId => ['stock' => max(0, (int)$qty)]
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'تم الحذف');
    }

    private function parseImageUrls(?string $input): array
    {
        if (empty(trim((string) $input))) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", $input)), fn($url) => !empty($url)));
    }
}
