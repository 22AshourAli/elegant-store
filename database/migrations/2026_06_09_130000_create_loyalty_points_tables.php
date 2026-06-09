<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add points to existing customer_wallets
        Schema::table('customer_wallets', function (Blueprint $table) {
            $table->integer('loyalty_points')->default(0)->after('balance');
            $table->decimal('lifetime_spent', 12, 2)->default(0)->after('loyalty_points');
        });

        // Points history log
        Schema::create('loyalty_points_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('points');
            $table->string('type')->comment('earned|spent|expired|adjustment');
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_points_log');
        Schema::table('customer_wallets', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points', 'lifetime_spent']);
        });
    }
};
