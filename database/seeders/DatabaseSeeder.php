<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@example.com'], // checks if a user already exists
            [
                'name' => 'Admin',
                'password' => Hash::make('123456'), // securely hashes the password
            ]
        );
    }
}
