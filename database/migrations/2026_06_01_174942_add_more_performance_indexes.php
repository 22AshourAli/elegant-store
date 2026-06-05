<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function createIndexIfNotExists(string $table, string $column, ?string $indexName = null): void
    {
        $index = $indexName ?? "{$table}_{$column}_index";
        DB::statement("CREATE INDEX IF NOT EXISTS \"{$index}\" ON \"{$table}\" (\"{$column}\")");
    }

    public function up(): void
    {
        $this->createIndexIfNotExists('orders', 'user_id');
        $this->createIndexIfNotExists('orders', 'payment_method');
        $this->createIndexIfNotExists('orders', 'payment_status');
        $this->createIndexIfNotExists('order_items', 'order_id');
        $this->createIndexIfNotExists('order_items', 'product_variant_id');
        $this->createIndexIfNotExists('payments', 'order_id');
        $this->createIndexIfNotExists('payments', 'status');
        $this->createIndexIfNotExists('return_requests', 'order_id');
        $this->createIndexIfNotExists('return_requests', 'user_id');
        $this->createIndexIfNotExists('return_requests', 'status');
        $this->createIndexIfNotExists('return_requests', 'type');
        $this->createIndexIfNotExists('branch_product_variant', 'branch_id');
        $this->createIndexIfNotExists('branch_product_variant', 'product_variant_id');
        $this->createIndexIfNotExists('wishlists', 'user_id');
        $this->createIndexIfNotExists('wishlists', 'product_id');
        $this->createIndexIfNotExists('coupons', 'code');
        $this->createIndexIfNotExists('coupons', 'is_active');
        $this->createIndexIfNotExists('notifications', 'notifiable_id');
        $this->createIndexIfNotExists('notifications', 'read_at');
        $this->createIndexIfNotExists('media', 'model_type');
        $this->createIndexIfNotExists('media', 'model_id');
        $this->createIndexIfNotExists('media', 'collection_name');
    }

    public function down(): void
    {
        // No-op: we don't drop indexes on rollback for safety
    }
};
