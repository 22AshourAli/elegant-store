<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::create([
            'category_id' => 2, // تيشيرتات
            'name' => 'تيشيرت بولو كلاسيك',
            'slug' => Str::slug('تيشيرت بولو كلاسيك'),
            'description' => 'تيشيرت بولو أنيق مصنوع من القطن الفاخر.',
            'base_price' => 250.00,
            'sale_price' => 199.00,
            'discount_start' => now(),
            'discount_end' => now()->addDays(10),
            'has_variants' => true,
            'featured' => true,
        ]);

        $colors = ['أبيض', 'أسود', 'كحلي'];
        $sizes = ['S', 'M', 'L', 'XL'];

        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => "BP-{$color}-{$size}",
                    'color' => $color,
                    'size' => $size,
                    'price_override' => null, // يرث base_price
                    'sale_price' => null, // يرث الخصم من المنتج
                ]);
                // تعيين مخزون عشوائي للفرع الرئيسي (id=1)
                $variant->branches()->attach(1, ['stock' => rand(1, 10)]);
            }
        }
    }
}
