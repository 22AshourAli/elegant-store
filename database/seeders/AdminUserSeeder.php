<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('ADMIN_PASSWORD');

        $admin = User::firstOrCreate(
            ['email' => 'ashouraligpt@gmail.com'],
            [
                'name' => 'Store Admin',
                'email' => 'ashouraligpt@gmail.com',
                'role' => 'super_admin',
                'branch_id' => null,
                'phone' => config('store.admin_phone', env('ADMIN_PHONE')),
                'password' => Hash::make($adminPassword ?: 'ChangeMe123!'),
            ]
        );

        if ($adminPassword) {
            $admin->password = Hash::make($adminPassword);
            $admin->save();
        }

        if (!Branch::exists()) {
            Branch::create([
                'name' => 'الفرع الرئيسي',
                'address' => 'الموقع الرئيسي',
                'is_active' => true,
                'phone' => '01000000000',
            ]);
        }

        User::firstOrCreate(
            ['email' => 'manager@store.com'],
            [
                'name' => 'Branch Manager',
                'email' => 'manager@store.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'branch_id' => 1,
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@store.com'],
            [
                'name' => 'Demo Customer',
                'email' => 'customer@store.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'branch_id' => null,
            ]
        );
    }
}
