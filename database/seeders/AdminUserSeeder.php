<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'lewis@mailinator.com'],
            [
                'name'     => 'Lewis Hamilton',
                'password' => Hash::make('Admin@123'),
                'type'     => 'admin',
                'status'     => 1,
                'email_verified_at'     => now(),
            ]
        );
    }
}
