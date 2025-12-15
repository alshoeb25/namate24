<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create student, tutor, and admin roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roles = ['student', 'tutor', 'admin'];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $this->info("Role '{$roleName}' created/exists.");
        }

        $this->info('All roles created successfully!');
        return 0;
    }
}
