<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_ajax_operations_work_correctly()
    {
        $branch = Branch::create(['name' => 'Branch 1', 'is_active' => true]);
        $category = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
            'is_active' => true
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'T-Shirt',
            'slug' => 't-shirt',
            'base_price' => 100,
            'is_active' => true
        ]);
        $variant = $product->variants()->create([
            'sku' => 'TS-RED-L',
            'color' => 'Red',
            'size' => 'L',
            'is_active' => true,
        ]);
        $variant->branches()->attach($branch->id, ['stock' => 10]);

        // 1. Add to cart
        $response = $this->postJson(route('cart.add', $variant), ['quantity' => 2]);
        $response->assertOk()
            ->assertJsonStructure(['message', 'cartCount']);

        $this->assertEquals(2, session('cart')[$variant->id]['quantity']);

        // 2. Update cart
        $response = $this->patchJson(route('cart.update', $variant), ['quantity' => 5]);
        $response->assertOk()
            ->assertJsonStructure(['total', 'cartCount']);

        $this->assertEquals(5, session('cart')[$variant->id]['quantity']);

        // 3. Remove from cart
        $response = $this->deleteJson(route('cart.remove', $variant));
        $response->assertOk()
            ->assertJsonStructure(['message', 'cartCount', 'total']);

        $this->assertArrayNotHasKey($variant->id, session('cart', []));
    }
}
