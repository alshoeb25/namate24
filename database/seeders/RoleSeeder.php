<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles with guard_name for API
        $roles = [
            ['name' => 'admin', 'guard_name' => 'api'],
            ['name' => 'tutor', 'guard_name' => 'api'],
            ['name' => 'student', 'guard_name' => 'api'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
        }

        $this->command->info('✓ Roles created successfully for API guard!');
        $this->command->info('Available roles: admin, tutor, student');
    }
}
