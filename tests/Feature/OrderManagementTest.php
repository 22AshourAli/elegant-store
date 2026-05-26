<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_order_status_and_restore_stock()
    {
        // 1. Create admin and normal user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@elegant.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@elegant.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        // 2. Create branch, category, product and variant
        $branch = Branch::create(['name' => 'Cairo Branch', 'address' => 'Cairo']);
        $category = \App\Models\Category::create(['name' => 'Suits', 'slug' => 'suits']);
        $product = Product::create([
            'name' => 'Test Suit',
            'slug' => 'test-suit',
            'description' => 'A fine suit',
            'base_price' => 1000,
            'category_id' => $category->id,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Navy',
            'size' => 'L',
            'additional_price' => 0,
        ]);
        $variant->branches()->attach($branch->id, ['stock' => 10]);

        // 3. Create Order
        $order = Order::create([
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'subtotal' => 1000,
            'total' => 1000,
            'shipping_address' => 'Cairo, Egypt',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Test Suit',
            'color' => 'Navy',
            'size' => 'L',
            'quantity' => 2,
            'unit_price' => 1000,
            'total' => 2000,
        ]);

        $order->payment()->create([
            'amount' => 1000,
            'status' => 'pending',
            'gateway' => 'cash',
        ]);

        // 4. Update order status as admin (to cancelled)
        $response = $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $order), [
                'status' => 'cancelled',
            ]);

        $response->assertRedirect();
        
        // 5. Assert database updates
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // Pivot stock should restore to 10 + 2 = 12
        $pivot = $variant->branches()->where('branch_id', $branch->id)->first()->pivot;
        $this->assertEquals(12, $pivot->stock);

        // Check if user has a notification
        $this->assertCount(1, $customer->notifications);
        $notification = $customer->notifications->first();
        $this->assertEquals($order->id, $notification->data['order_id']);
        $this->assertEquals('cancelled', $notification->data['status']);
    }
}
