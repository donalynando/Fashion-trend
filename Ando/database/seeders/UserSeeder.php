<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'lastname' => 'Admin',
            'firstname' => 'Super',
            'middlename' => '',
            'gender' => 'male',
            'age' => 30,
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'Active'
        ]);

        // Create test customer
        User::create([
            'lastname' => 'Doe',
            'firstname' => 'John',
            'middlename' => 'Smith',
            'gender' => 'male',
            'age' => 25,
            'email' => 'john.doe@example.com',
            'phone' => '9876543210',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'Active'
        ]);

        // Create test customer 2
        User::create([
            'lastname' => 'Smith',
            'firstname' => 'Jane',
            'middlename' => 'Marie',
            'gender' => 'female',
            'age' => 28,
            'email' => 'jane.smith@example.com',
            'phone' => '5555555555',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'Active'
        ]);
    }
}
