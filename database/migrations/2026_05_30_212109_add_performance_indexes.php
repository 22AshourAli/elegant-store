<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('featured');
            $table->index('slug');
            $table->index('base_price');
            $table->index('created_at');
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index('color');
            $table->index('size');
            $table->index('is_active');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
            $table->index('branch_id');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug');
            $table->index('is_active');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'featured', 'slug', 'base_price', 'created_at']);
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['color', 'size', 'is_active']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at', 'branch_id']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug', 'is_active', 'parent_id']);
        });
    }
};
