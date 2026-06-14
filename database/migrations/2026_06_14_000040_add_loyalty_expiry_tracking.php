<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_points_log', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('meta');
        });

        Schema::table('customer_wallets', function (Blueprint $table) {
            $table->integer('points_expired')->default(0)->after('loyalty_points');
            $table->timestamp('last_expiry_notified_at')->nullable()->after('points_expired');
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_points_log', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });

        Schema::table('customer_wallets', function (Blueprint $table) {
            $table->dropColumn(['points_expired', 'last_expiry_notified_at']);
        });
    }
};
