<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products: covering indexes for cursor pagination on (is_active, sort_field, id)
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'created_at', 'id'], 'products_active_created_id_idx');
            $table->index(['is_active', 'name', 'id'], 'products_active_name_id_idx');
            $table->index(['is_active', 'base_price', 'id'], 'products_active_price_id_idx');
            $table->index(['is_active', 'category_id', 'created_at', 'id'], 'products_active_cat_created_id_idx');
        });

        // Orders: covering indexes for order history queries
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at', 'id'], 'orders_user_created_id_idx');
            $table->index(['status', 'created_at', 'id'], 'orders_status_created_id_idx');
            $table->index(['payment_status', 'created_at', 'id'], 'orders_paystatus_created_id_idx');
            $table->index(['branch_id', 'created_at', 'id'], 'orders_branch_created_id_idx');
            $table->index(['created_at', 'id'], 'orders_created_id_idx');
        });

        // Order items: index for order detail queries
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'id'], 'order_items_order_id_idx');
        });

        // Product variants: covering index for variant queries
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'is_active', 'id'], 'variants_product_active_id_idx');
        });

        // Return requests: covering indexes
        Schema::table('return_requests', function (Blueprint $table) {
            $table->index(['user_id', 'created_at', 'id'], 'returns_user_created_id_idx');
            $table->index(['status', 'created_at', 'id'], 'returns_status_created_id_idx');
        });

        // Exchanges: covering indexes
        Schema::table('exchanges', function (Blueprint $table) {
            $table->index(['user_id', 'created_at', 'id'], 'exchanges_user_created_id_idx');
            $table->index(['status', 'created_at', 'id'], 'exchanges_status_created_id_idx');
        });

        // Notifications (UUID PK, morph columns): index for cursor pagination
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'notifiable_type', 'created_at'], 'notifications_morph_created_idx');
        });

        // Wishlists: covering index for wishlist listing
        Schema::table('wishlists', function (Blueprint $table) {
            $table->index(['user_id', 'created_at', 'id'], 'wishlists_user_created_id_idx');
        });

        // Stock movements: covering index for audit/history
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['product_variant_id', 'created_at', 'id'], 'stockmov_var_created_id_idx');
        });

        // Purchase orders: covering indexes
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['status', 'created_at', 'id'], 'po_status_created_id_idx');
            $table->index(['supplier_id', 'created_at', 'id'], 'po_supplier_created_id_idx');
        });

        // Stock transfers: covering indexes
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->index(['status', 'created_at', 'id'], 'st_status_created_id_idx');
        });

        // Expenses: covering index
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['branch_id', 'expense_date', 'id'], 'expenses_branch_date_id_idx');
        });

        // Customers (users) listing: covering indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'created_at', 'id'], 'users_role_created_id_idx');
        });

        // Branch product variant pivot: index for stock queries
        Schema::table('branch_product_variant', function (Blueprint $table) {
            $table->index(['product_variant_id', 'branch_id'], 'bpv_variant_branch_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_created_id_idx');
            $table->dropIndex('products_active_name_id_idx');
            $table->dropIndex('products_active_price_id_idx');
            $table->dropIndex('products_active_cat_created_id_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_created_id_idx');
            $table->dropIndex('orders_status_created_id_idx');
            $table->dropIndex('orders_paystatus_created_id_idx');
            $table->dropIndex('orders_branch_created_id_idx');
            $table->dropIndex('orders_created_id_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_id_idx');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('variants_product_active_id_idx');
        });

        Schema::table('return_requests', function (Blueprint $table) {
            $table->dropIndex('returns_user_created_id_idx');
            $table->dropIndex('returns_status_created_id_idx');
        });

        Schema::table('exchanges', function (Blueprint $table) {
            $table->dropIndex('exchanges_user_created_id_idx');
            $table->dropIndex('exchanges_status_created_id_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_morph_created_idx');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex('wishlists_user_created_id_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stockmov_var_created_id_idx');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('po_status_created_id_idx');
            $table->dropIndex('po_supplier_created_id_idx');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropIndex('st_status_created_id_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_branch_date_id_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_created_id_idx');
        });

        Schema::table('branch_product_variant', function (Blueprint $table) {
            $table->dropIndex('bpv_variant_branch_idx');
        });
    }
};
