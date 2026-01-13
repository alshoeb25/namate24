<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RemoveAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:remove-role {email} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove an admin role from a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found with email: {$email}");
            return self::FAILURE;
        }

        // Check if user has this role
        if (!$user->hasRole($role)) {
            $this->warn("User {$email} does not have the role: {$role}");
            return self::SUCCESS;
        }

        // Remove role
        $user->removeRole($role);

        $this->info("✓ Successfully removed role '{$role}' from {$email}");
        
        if ($user->roles->count() > 0) {
            $this->info("User now has roles: " . $user->getRoleNames()->implode(', '));
        } else {
            $this->warn("User now has no roles assigned");
        }

        return self::SUCCESS;
    }
}
