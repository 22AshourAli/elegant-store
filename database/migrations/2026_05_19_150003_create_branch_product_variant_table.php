<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('branch_product_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('stock')->default(0);
            $table->unique(['branch_id', 'product_variant_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branch_product_variant');
    }
};
