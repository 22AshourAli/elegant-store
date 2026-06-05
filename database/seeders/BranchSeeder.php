<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $branches = [
            ['name' => 'الفرع الرئيسي', 'address' => 'القاهرة', 'phone' => '0100000000'],
            ['name' => 'فرع الجيزة', 'address' => 'الجيزة', 'phone' => '0111111111'],
            ['name' => 'فرع الإسكندرية', 'address' => 'الإسكندرية', 'phone' => '0122222222'],
        ];
        foreach ($branches as $b) {
            DB::insert(
                'INSERT INTO branches (name, address, phone, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
                [$b['name'], $b['address'], $b['phone'], true, $now, $now]
            );
        }
    }
}
