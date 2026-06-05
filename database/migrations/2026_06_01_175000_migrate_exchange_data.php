<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exchanges = DB::select("SELECT id, order_id, user_id, status, reason, exchange_data, admin_note, approved_at, rejected_at, created_at, updated_at FROM return_requests WHERE type = ?", ['exchange']);

        foreach ($exchanges as $row) {
            DB::insert(
                "INSERT INTO exchanges (id, order_id, user_id, status, reason, items, admin_note, approved_at, rejected_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $row->id,
                    $row->order_id,
                    $row->user_id,
                    $row->status,
                    $row->reason,
                    $row->exchange_data,
                    $row->admin_note,
                    $row->approved_at,
                    $row->rejected_at,
                    $row->created_at,
                    $row->updated_at,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::delete("DELETE FROM exchanges WHERE id IN (SELECT id FROM return_requests WHERE type = ?)", ['exchange']);
    }
};
