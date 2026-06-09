<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('delivery_time')->nullable()->comment('e.g. 2-3 business days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['governorate_id', 'is_active']);
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('min_cart_amount', 10, 2)->nullable()->comment('Free shipping above this amount');
            $table->decimal('rate', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['governorate_id', 'city_id', 'is_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('governorate_id')->nullable()->constrained()->nullOnDelete()->after('shipping_address');
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete()->after('governorate_id');
            $table->string('courier_name')->nullable()->after('tracking_number');
            $table->string('tracking_url')->nullable()->after('courier_name');
            $table->string('shipping_status')->default('pending')->after('tracking_url')
                ->comment('pending|picked_up|in_transit|delivered|failed');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['governorate_id', 'city_id', 'courier_name', 'tracking_url', 'shipping_status']);
        });
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('governorates');
    }
};
