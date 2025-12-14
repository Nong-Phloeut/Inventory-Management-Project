<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'full_name' => 'Admin User',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role_id' => 1, // Administrator
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Manager User',
                'email' => 'manager@example.com',
                'username' => 'manager',
                'password' => Hash::make('password123'),
                'role_id' => 2, // Manager
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Purchaser User',
                'email' => 'purchaser@example.com',
                'username' => 'purchaser',
                'password' => Hash::make('password123'),
                'role_id' => 3, // Purchaser
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
