<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@finditfast.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@finditfast.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'is_verified' => true,
            ]
        );

        // Create regular user
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'email' => 'user@example.com',
                'password' => Hash::make('user123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin: admin@finditfast.com / admin123');
        $this->command->info('User: user@example.com / user123');
    }
}

