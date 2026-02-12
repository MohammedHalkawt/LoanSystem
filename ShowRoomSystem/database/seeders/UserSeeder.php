<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{


    public function run(): void
    {
        User::create([
            'name' => 'Main Editor',
            'email' => 'editor@company.com',
            'password' => '123456',
            'role' => 'editor'
        ]);

        User::create([
            'name' => 'Main Viewer',
            'email' => 'viewer@company.com',
            'password' => '123456',
            'role' => 'viewer'
        ]);
    }

}
