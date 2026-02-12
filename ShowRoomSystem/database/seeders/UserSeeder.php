<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Main Editor',
            'email' => 'editor@company.com',
            'password' => Hash::make('123456'),   // 👈 hashed
            'role' => 'editor'
        ]);

        User::create([
            'name' => 'Main Viewer',
            'email' => 'viewer@company.com',
            'password' => Hash::make('123456'),   // 👈 hashed
            'role' => 'viewer'
        ]);
    }
}