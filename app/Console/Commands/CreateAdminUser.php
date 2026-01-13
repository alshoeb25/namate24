<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create-user {name} {email} {role}';
    protected $description = 'Create a new admin user with a specific role';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $role = $this->argument('role');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists");
            return 1;
        }

        // Check if role exists
        if (!Role::where('name', $role)->exists()) {
            $this->error("Role {$role} does not exist");
            return 1;
        }

        // Get password from input
        $password = $this->secret('Enter password for the new admin user');
        $passwordConfirm = $this->secret('Confirm password');

        if ($password !== $passwordConfirm) {
            $this->error('Passwords do not match');
            return 1;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => now(),
        ]);

        // Assign role
        $user->assignRole($role);

        $this->info("Successfully created admin user '{$name}' with email '{$email}' and role '{$role}'");
        return 0;
    }
}
