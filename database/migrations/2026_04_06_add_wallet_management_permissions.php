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
        // Create permissions if they don't exist
        $permissionsToCreate = [
            // Coin Packages Permissions
            'view-coin-packages',
            'create-coin-packages',
            'edit-coin-packages',
            'delete-coin-packages',
            
            // Wallet Management Permissions
            'view-wallet-management',
            
            // Subscription Plans Permissions (if not already created)
            'view-subscription-plans',
            'create-subscription-plans',
            'edit-subscription-plans',
            'delete-subscription-plans',
            
            // Coin Transactions (Wallet) Permissions
            'view-coin-transactions',
            
            // Orders/Payments Permissions
            'view-orders',
        ];

        foreach ($permissionsToCreate as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Get super admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $walletManagerRole = Role::firstOrCreate(['name' => 'wallet_manager', 'guard_name' => 'web']);

        // Assign all permissions to super_admin
        $superAdminRole->syncPermissions($permissionsToCreate);

        // Assign wallet-specific permissions to wallet_manager
        $walletPermissions = [
            'view-coin-packages',
            'create-coin-packages',
            'edit-coin-packages',
            'delete-coin-packages',
            'view-wallet-management',
            'view-coin-transactions',
            'view-subscription-plans',
            'edit-subscription-plans',
            'view-orders',
        ];
        $walletManagerRole->syncPermissions($walletPermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove created permissions
        Permission::whereIn('name', [
            'view-coin-packages',
            'create-coin-packages',
            'edit-coin-packages',
            'delete-coin-packages',
            'view-wallet-management',
            'view-subscription-plans',
            'create-subscription-plans',
            'edit-subscription-plans',
            'delete-subscription-plans',
            'view-coin-transactions',
            'view-orders',
        ])->delete();
    }
};
