<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('courier'); // inhouse, courier
            $table->string('phone')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->nullable(); // district, village, area
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('shipping_rates', function (Blueprint $table) {
            $table->foreignId('shipping_provider_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
            $table->integer('estimated_days')->nullable()->after('rate');
        });

        // Insert default inhouse provider
        DB::table('shipping_providers')->insert([
            'name' => 'توصيل داخلي',
            'type' => 'inhouse',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('shipping_rates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_provider_id');
            $table->dropConstrainedForeignId('district_id');
            $table->dropColumn('estimated_days');
        });

        Schema::dropIfExists('districts');
        Schema::dropIfExists('shipping_providers');
    }
};
