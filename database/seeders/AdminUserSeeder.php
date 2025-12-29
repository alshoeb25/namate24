<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user if it doesn't exist
        $admin = User::where('email', 'admin@namate24.com')->first();

        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@namate24.com',
                'password' => Hash::make('admin@123456'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'phone' => '+919999999999',
            ]);

            $admin->assignRole('admin');
            echo "Admin user created: admin@namate24.com / admin@123456\n";
        } else {
            echo "Admin user already exists.\n";
        }
    }
}
