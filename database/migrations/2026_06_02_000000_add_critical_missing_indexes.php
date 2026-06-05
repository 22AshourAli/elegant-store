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
        // ---- users ----
        $this->createIndexIfNotExists('users', 'branch_id');
        $this->createIndexIfNotExists('users', 'role');
        $this->createIndexIfNotExists('users', 'phone');
        $this->createIndexIfNotExists('users', 'social_id');
        $this->createIndexIfNotExists('users', 'social_type');

        // ---- branches ----
        $this->createIndexIfNotExists('branches', 'is_active');

        // ---- products ----
        $this->createIndexIfNotExists('products', 'category_id');
        $this->createIndexIfNotExists('products', 'sale_price');
        $this->createIndexIfNotExists('products', 'has_variants');

        // ---- product_variants ----
        $this->createIndexIfNotExists('product_variants', 'product_id');
        $this->createIndexIfNotExists('product_variants', 'is_default');

        // ---- orders ----
        $this->createIndexIfNotExists('orders', 'tracking_number');
        $this->createIndexIfNotExists('orders', 'phone');
        $this->createIndexIfNotExists('orders', 'delivered_at');

        // ---- coupons ----
        $this->createIndexIfNotExists('coupons', 'type');
        $this->createIndexIfNotExists('coupons', 'valid_from');
        $this->createIndexIfNotExists('coupons', 'valid_until');

        // ---- payments ----
        $this->createIndexIfNotExists('payments', 'transaction_id');
        $this->createIndexIfNotExists('payments', 'gateway');

        // ---- notifications ----
        $this->createIndexIfNotExists('notifications', 'type');

        // ---- exchanges (zero indexes before this) ----
        $this->createIndexIfNotExists('exchanges', 'order_id');
        $this->createIndexIfNotExists('exchanges', 'user_id');
        $this->createIndexIfNotExists('exchanges', 'status');
        $this->createIndexIfNotExists('exchanges', 'created_at');

        // ---- customer_wallets (zero indexes before this) ----
        $this->createIndexIfNotExists('customer_wallets', 'user_id');

        // ---- expenses (zero indexes before this) ----
        $this->createIndexIfNotExists('expenses', 'branch_id');
        $this->createIndexIfNotExists('expenses', 'created_by');
        $this->createIndexIfNotExists('expenses', 'category');
        $this->createIndexIfNotExists('expenses', 'expense_date');

        // ---- whatsapp_logs (zero indexes before this) ----
        $this->createIndexIfNotExists('whatsapp_logs', 'user_id');
        $this->createIndexIfNotExists('whatsapp_logs', 'sent_by');
        $this->createIndexIfNotExists('whatsapp_logs', 'message_type');
        $this->createIndexIfNotExists('whatsapp_logs', 'status');
        $this->createIndexIfNotExists('whatsapp_logs', 'sent_at');
        $this->createIndexIfNotExists('whatsapp_logs', 'created_at');
    }

    public function down(): void
    {
        // No-op: we don't drop indexes on rollback for safety
    }
};
