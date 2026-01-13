<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AddAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:add-role {email} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add an additional admin role to an existing user';

    /**
     * Available admin roles
     */
    protected array $availableRoles = [
        'super_admin',
        'coin_wallet_admin',
        'student_admin',
        'tutor_admin',
        'enquiries_admin',
        'reviews_admin',
        'service_admin',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        // Validate role
        if (!in_array($role, $this->availableRoles)) {
            $this->error("Invalid role: {$role}");
            $this->info("Available roles: " . implode(', ', $this->availableRoles));
            return self::FAILURE;
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found with email: {$email}");
            return self::FAILURE;
        }

        // Check if user already has this role
        if ($user->hasRole($role)) {
            $this->warn("User {$email} already has the role: {$role}");
            return self::SUCCESS;
        }

        // Assign role
        $user->assignRole($role);

        $this->info("✓ Successfully assigned role '{$role}' to {$email}");
        $this->info("User now has roles: " . $user->getRoleNames()->implode(', '));

        return self::SUCCESS;
    }
}
