<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the view-dashboard permission for both guards
        Permission::firstOrCreate(['name' => 'view-dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view-dashboard', 'guard_name' => 'api']);

        // Get super admin role (web guard) and assign permission
        $superAdminRole = Role::where('name', 'super_admin')->where('guard_name', 'web')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo('view-dashboard');
        }

        // Also ensure admin role has this permission (for backward compatibility)
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo('view-dashboard');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the permissions
        Permission::where('name', 'view-dashboard')->delete();
    }
};
