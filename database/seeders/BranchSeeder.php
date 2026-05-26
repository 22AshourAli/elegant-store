<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'الفرع الرئيسي',
            'address' => 'القاهرة',
            'phone' => '0100000000',
            'is_active' => true,
        ]);

        Branch::create([
            'name' => 'فرع الجيزة',
            'address' => 'الجيزة',
            'phone' => '0111111111',
            'is_active' => true,
        ]);

        Branch::create([
            'name' => 'فرع الإسكندرية',
            'address' => 'الإسكندرية',
            'phone' => '0122222222',
            'is_active' => true,
        ]);
    }
}
