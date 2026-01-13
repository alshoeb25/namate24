<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListAdminRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:list-roles {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List admin roles for a user or all admin users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        if ($email) {
            return $this->showUserRoles($email);
        }

        return $this->showAllAdminUsers();
    }

    /**
     * Show roles for a specific user
     */
    protected function showUserRoles(string $email): int
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found with email: {$email}");
            return self::FAILURE;
        }

        $this->info("User: {$user->name} ({$user->email})");
        
        if ($user->roles->count() === 0) {
            $this->warn("No roles assigned");
            return self::SUCCESS;
        }

        $this->info("Roles: " . $user->getRoleNames()->implode(', '));
        
        // Show permissions
        $permissions = $user->getAllPermissions()->pluck('name');
        if ($permissions->count() > 0) {
            $this->info("\nPermissions (" . $permissions->count() . "):");
            foreach ($permissions->chunk(3) as $chunk) {
                $this->line("  • " . $chunk->implode(', '));
            }
        }

        return self::SUCCESS;
    }

    /**
     * Show all admin users and their roles
     */
    protected function showAllAdminUsers(): int
    {
        $adminRoles = [
            'super_admin',
            'coin_wallet_admin',
            'student_admin',
            'tutor_admin',
            'enquiries_admin',
            'reviews_admin',
            'service_admin',
        ];

        $adminUsers = User::whereHas('roles', function ($query) use ($adminRoles) {
            $query->whereIn('name', $adminRoles);
        })->with('roles')->get();

        if ($adminUsers->isEmpty()) {
            $this->warn("No admin users found");
            return self::SUCCESS;
        }

        $this->info("=== Admin Users and Their Roles ===\n");

        $headers = ['Name', 'Email', 'Roles', 'Role Count'];
        $rows = [];

        foreach ($adminUsers as $user) {
            $rows[] = [
                $user->name,
                $user->email,
                $user->getRoleNames()->implode(', '),
                $user->roles->count(),
            ];
        }

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}
