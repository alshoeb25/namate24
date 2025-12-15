<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign roles to existing users based on their role column';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        
        foreach ($users as $user) {
            // Remove all existing roles first
            $user->syncRoles([]);
            
            // Assign role based on the role column (default is 'student')
            $roleName = $user->role ?? 'student';
            
            try {
                $user->assignRole($roleName);
                $this->info("Assigned role '{$roleName}' to user: {$user->email}");
            } catch (\Exception $e) {
                $this->error("Failed to assign role '{$roleName}' to user: {$user->email} - " . $e->getMessage());
            }
        }

        $this->info('Finished assigning roles to all users!');
        return 0;
    }
}
