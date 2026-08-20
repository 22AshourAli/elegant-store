<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $variants = DB::table('product_variants')->whereNull('barcode')->get();

        foreach ($variants as $variant) {
            do {
                $code = str_pad(mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
            } while (DB::table('product_variants')->where('barcode', $code)->exists());

            DB::table('product_variants')
                ->where('id', $variant->id)
                ->update(['barcode' => $code]);
        }
    }

    public function down(): void
    {
        DB::table('product_variants')->update(['barcode' => null]);
    }
};
