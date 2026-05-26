<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('ADMIN_PASSWORD');

        $admin = User::firstOrCreate(
            ['email' => 'admin@store.com'],
            [
                'name' => 'Store Admin',
                'email' => 'admin@store.com',
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

        User::firstOrCreate(
            ['email' => 'manager@store.com'],
            [
                'name' => 'Branch Manager',
                'email' => 'manager@store.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'branch_id' => null,
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
