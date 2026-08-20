<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix the inventory_count_items table if it exists with wrong constraints.
     * This migration handles the case where the table was partially created
     * but the unique constraint failed due to identifier length.
     */
    public function up(): void
    {
        // If table doesn't exist, create it properly
        if (!Schema::hasTable('inventory_count_items')) {
            Schema::create('inventory_count_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_count_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
                $table->integer('system_stock')->default(0);
                $table->integer('counted_stock')->nullable();
                $table->integer('difference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['inventory_count_id', 'product_variant_id'], 'inv_count_variant_unique');
            });
        } else {
            // Table exists but might not have the unique constraint
            if (!$this->hasUniqueConstraint('inventory_count_items', 'inv_count_variant_unique')) {
                Schema::table('inventory_count_items', function (Blueprint $table) {
                    $table->unique(['inventory_count_id', 'product_variant_id'], 'inv_count_variant_unique');
                });
            }
        }
    }

    public function down(): void
    {
        // Don't drop the table on rollback—it has real data
        // This is a fix migration, not a create migration
    }

    /**
     * Check if a unique constraint exists on a table.
     */
    private function hasUniqueConstraint(string $table, string $constraintName): bool
    {
        try {
            $constraints = \DB::select(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = ? AND CONSTRAINT_NAME = ?",
                [$table, $constraintName]
            );
            return count($constraints) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};

