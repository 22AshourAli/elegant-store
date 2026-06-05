<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(20);
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
            'variant_prices' => 'nullable|array',
            'variant_sale_prices' => 'nullable|array',
            'variant_stocks' => 'nullable|array', 
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,webp,jpg|max:2048',
            'primary_image' => 'nullable|integer', 
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        $validated['has_variants'] = $request->has('has_variants');
        $validated['featured'] = $request->has('featured');
        $validated['is_active'] = $request->has('is_active');

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $media = $product->addMedia($image)->toMediaCollection('product_images');
                if ($request->input('primary_image') == $index) {
                    $media->setCustomProperty('primary', true);
                    $media->save();
                }
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
                        $variant = $product->variants()->create([
                            'color' => $color,
                            'size' => $size,
                            'sku' => $product->id . '-' . $color . '-' . $size,
                            'price_override' => $validated['variant_prices'][$key] ?? null,
                            'sale_price' => $validated['variant_sale_prices'][$key] ?? null,
                        ]);

                        $stocks = $validated['variant_stocks'][$key] ?? [];
                        foreach ($stocks as $branchId => $qty) {
                            $variant->branches()->attach($branchId, ['stock' => max(0, (int)$qty)]);
                        }
                    }
                }
            }
        } else {
            $variant = $product->variants()->create([
                'is_default' => true,
                'price_override' => null,
            ]);
            
            $stocks = $validated['variant_stocks']['default'] ?? [];
            foreach (Branch::all() as $branch) {
                $qty = $stocks[$branch->id] ?? 0;
                $variant->branches()->attach($branch->id, ['stock' => max(0, (int)$qty)]);
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
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,webp,jpg|max:2048',
        ]);

        $validated['featured'] = $request->has('featured');
        $validated['is_active'] = $request->has('is_active');

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
                    'sale_price' => empty($vData['sale_price']) ? null : $vData['sale_price'],
                    'sku' => $vData['sku'] ?? null,
                ]);

                if (!empty($vData['delete_image']) && $variant->hasMedia('variant_images')) {
                    $variant->clearMediaCollection('variant_images');
                }

                if ($request->hasFile("variants.{$variantId}.image")) {
                    $variant->clearMediaCollection('variant_images');
                    $variant->addMediaFromRequest("variants.{$variantId}.image")->toMediaCollection('variant_images');
                }

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
}
