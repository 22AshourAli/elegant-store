<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::table('governorates', function (Blueprint $table) {
            $table->decimal('base_shipping_cost', 10, 2)->default(0)->after('is_active');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('address_street')->nullable()->after('shipping_address');
            $table->string('address_building')->nullable()->after('address_street');
            $table->string('address_floor')->nullable()->after('address_building');
            $table->string('address_apartment')->nullable()->after('address_floor');
            $table->string('address_landmark')->nullable()->after('address_apartment');
            $table->string('address_type')->nullable()->comment('village, main_street')->after('address_landmark');
        });

        DB::table('settings')->insert([
            ['key' => 'fuel_surcharge_percentage', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'free_shipping_threshold', 'value' => '500', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_shipping_cost', 'value' => '30', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('governorates', function (Blueprint $table) {
            $table->dropColumn('base_shipping_cost');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['address_street', 'address_building', 'address_floor', 'address_apartment', 'address_landmark', 'address_type']);
        });
        Schema::dropIfExists('settings');
    }
};
