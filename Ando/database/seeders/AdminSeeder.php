<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'lastname' => 'Admin',
            'firstname' => 'Super',
            'middlename' => 'User',
            'gender' => 'other',
            'age' => 25,
            'phone' => '1234567890',
            'password' => Hash::make('admin123')
        ]);
    }
} 