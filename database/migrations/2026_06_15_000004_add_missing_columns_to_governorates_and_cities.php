<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('governorates', function (Blueprint $table) {
            if (!Schema::hasColumn('governorates', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (!Schema::hasColumn('governorates', 'base_shipping_cost')) {
                $table->decimal('base_shipping_cost', 10, 2)->default(0)->after('is_active');
            }
        });

        Schema::table('cities', function (Blueprint $table) {
            if (!Schema::hasColumn('cities', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (!Schema::hasColumn('cities', 'base_shipping_cost')) {
                $table->decimal('base_shipping_cost', 10, 2)->nullable()->after('delivery_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('governorates', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'base_shipping_cost']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'base_shipping_cost']);
        });
    }
};
