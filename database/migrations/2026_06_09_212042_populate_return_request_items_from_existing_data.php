<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $returnRequests = DB::table('return_requests')->get();

        foreach ($returnRequests as $returnRequest) {
            $orderItems = DB::table('order_items')
                ->where('order_id', $returnRequest->order_id)
                ->where('returned_qty', '>', 0)
                ->get();

            foreach ($orderItems as $item) {
                $existing = DB::table('return_request_items')
                    ->where('return_request_id', $returnRequest->id)
                    ->where('order_item_id', $item->id)
                    ->exists();

                if (!$existing) {
                    DB::table('return_request_items')->insert([
                        'return_request_id' => $returnRequest->id,
                        'order_item_id' => $item->id,
                        'quantity' => $item->returned_qty,
                        'reason_category' => 'other',
                        'reason_detail' => $returnRequest->reason,
                        'created_at' => $returnRequest->created_at,
                        'updated_at' => $returnRequest->updated_at,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('return_request_items')->truncate();
    }
};
