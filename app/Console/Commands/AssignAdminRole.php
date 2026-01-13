<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignAdminRole extends Command
{
    protected $signature = 'admin:assign-role {email} {role}';
    protected $description = 'Assign an admin role to a user';

    public function handle()
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found");
            return 1;
        }

        $roleExists = Role::where('name', $role)->exists();

        if (!$roleExists) {
            $this->error("Role {$role} does not exist");
            return 1;
        }

        $user->assignRole($role);

        $this->info("Successfully assigned role '{$role}' to {$email}");
        return 0;
    }
}
