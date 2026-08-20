<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create inventory_count_items table.
     * Idempotent: handles the case where table partially exists from failed previous run.
     */
    public function up(): void
    {
        // Skip if table already exists (from partial previous deployment)
        if (Schema::hasTable('inventory_count_items')) {
            return;
        }

        Schema::create('inventory_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_count_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->integer('system_stock')->default(0);
            $table->integer('counted_stock')->nullable();
            $table->integer('difference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Use short constraint name to stay under MySQL's 64-char identifier limit
            $table->unique(['inventory_count_id', 'product_variant_id'], 'inv_count_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_count_items');
    }
};

