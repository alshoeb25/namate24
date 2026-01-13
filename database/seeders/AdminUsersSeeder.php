<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminEmail = config('app.super_admin_email') ?? 'admin@namate24.com';

        $adminUsers = [
            [
                'name' => 'Super Admin',
                'email' => $superAdminEmail,
                'password' => 'admin@123456',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Coin & Wallet Manager',
                'email' => 'coin@namate24.com',
                'password' => 'coin@123456',
                'role' => 'coin_wallet_admin',
            ],
            [
                'name' => 'Student Manager',
                'email' => 'student@namate24.com',
                'password' => 'student@123456',
                'role' => 'student_admin',
            ],
            [
                'name' => 'Tutor Manager',
                'email' => 'tutor@namate24.com',
                'password' => 'tutor@123456',
                'role' => 'tutor_admin',
            ],
            [
                'name' => 'Enquiries Manager',
                'email' => 'enquiries@namate24.com',
                'password' => 'enquiries@123456',
                'role' => 'enquiries_admin',
            ],
            [
                'name' => 'Reviews Manager',
                'email' => 'reviews@namate24.com',
                'password' => 'reviews@123456',
                'role' => 'reviews_admin',
            ],
            [
                'name' => 'Service Manager',
                'email' => 'service@namate24.com',
                'password' => 'service@123456',
                'role' => 'service_admin',
            ],
        ];

        foreach ($adminUsers as $adminData) {
            // Create or update user with new password
            $user = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => Hash::make($adminData['password']),
                    'email_verified_at' => now(), // Mark as verified immediately
                ]
            );

            $wasRecentlyCreated = $user->wasRecentlyCreated;
            if ($wasRecentlyCreated) {
                $this->command->info("✓ Created user: {$adminData['email']}");
            } else {
                $this->command->info("✓ Updated user: {$adminData['email']} (password refreshed)");
            }

            // Assign role (keeps existing roles, adds new one if not present)
            // Users can have multiple admin roles for cross-functional access
            if (!$user->hasRole($adminData['role'])) {
                $user->assignRole($adminData['role']);
                $this->command->info("✓ Assigned role '{$adminData['role']}' to {$adminData['email']}");
            } else {
                $this->command->info("✓ User {$adminData['email']} already has role '{$adminData['role']}'");
            }
        }

        $this->command->info("\n=== Admin Users Created Successfully ===");
        $this->command->info("\nLogin Credentials:");
        $this->command->line("┌─────────────────────────────┬──────────────────────────┬──────────────┐");
        $this->command->line("│ Role                        │ Email                    │ Password     │");
        $this->command->line("├─────────────────────────────┼──────────────────────────┼──────────────┤");
        
        foreach ($adminUsers as $admin) {
            $role = str_pad($admin['name'], 27);
            $email = str_pad($admin['email'], 24);
            $password = str_pad($admin['password'], 12);
            $this->command->line("│ {$role} │ {$email} │ {$password} │");
        }
        
        $this->command->line("└─────────────────────────────┴──────────────────────────┴──────────────┘");
    }
}
