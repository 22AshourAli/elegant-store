<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['sale_price', 'discount_start', 'discount_end']);
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable()->after('price_override');
            $table->timestamp('discount_start')->nullable()->after('sale_price');
            $table->timestamp('discount_end')->nullable()->after('discount_start');
        });
    }
};
