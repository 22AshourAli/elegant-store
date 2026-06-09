<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->json('items');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->string('status')->default('active')
                ->comment('active|abandoned|recovered|converted');
            $table->timestamp('first_abandoned_at')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->string('recovery_token')->nullable()->unique();
            $table->unsignedTinyInteger('reminder_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'first_abandoned_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
