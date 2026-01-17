<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions
        $permissions = [
            // Coin & Wallet Management
            'view-coins',
            'create-coins',
            'edit-coins',
            'delete-coins',
            'view-wallet',
            'manage-wallet',
            'view-transactions',
            'refund-coins',

            // Referral Invites Management
            'view-referral-invites',
            'create-referral-invites',
            'edit-referral-invites',
            'delete-referral-invites',

            // Referral Codes Management
            'view-referral-codes',
            'create-referral-codes',
            'edit-referral-codes',
            'delete-referral-codes',

            // Student Management
            'view-students',
            'create-students',
            'edit-students',
            'delete-students',
            'view-student-requirements',
            'manage-student-requirements',

            // Tutor Management
            'view-tutors',
            'create-tutors',
            'edit-tutors',
            'delete-tutors',
            'approve-tutors',
            'reject-tutors',

            // Enquiries Management
            'view-enquiries',
            'manage-enquiries',
            'respond-enquiries',

            // Reviews Management
            'view-reviews',
            'approve-reviews',
            'reject-reviews',
            'delete-reviews',

            // Service Management
            'view-services',
            'create-services',
            'edit-services',
            'delete-services',
            'view-orders',
            'manage-orders',

            // General Admin
            'view-dashboard',
            'access-settings',
            'view-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Create Roles and assign permissions

        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['guard_name' => 'web']
        );
        $superAdmin->syncPermissions($permissions);

        // Referral Manager
        $referralManager = Role::firstOrCreate(
            ['name' => 'referral_admin'],
            ['guard_name' => 'web']
        );
        $referralManager->syncPermissions([
            'view-referral-invites',
            'create-referral-invites',
            'edit-referral-invites',
            'delete-referral-invites',
            'view-referral-codes',
            'create-referral-codes',
            'edit-referral-codes',
            'delete-referral-codes',
            'view-dashboard',
        ]);

        // Coin & Wallet Manager
        $coinManager = Role::firstOrCreate(
            ['name' => 'coin_wallet_admin'],
            ['guard_name' => 'web']
        );
        $coinManager->syncPermissions([
            'view-coins',
            'create-coins',
            'edit-coins',
            'delete-coins',
            'view-wallet',
            'manage-wallet',
            'view-transactions',
            'refund-coins',
            'view-referral-invites',
            'create-referral-invites',
            'edit-referral-invites',
            'delete-referral-invites',
            'view-dashboard',
        ]);

        // Student Manager
        $studentManager = Role::firstOrCreate(
            ['name' => 'student_admin'],
            ['guard_name' => 'web']
        );
        $studentManager->syncPermissions([
            'view-students',
            'create-students',
            'edit-students',
            'delete-students',
            'view-student-requirements',
            'manage-student-requirements',
            'view-dashboard',
        ]);

        // Tutor Manager
        $tutorManager = Role::firstOrCreate(
            ['name' => 'tutor_admin'],
            ['guard_name' => 'web']
        );
        $tutorManager->syncPermissions([
            'view-tutors',
            'create-tutors',
            'edit-tutors',
            'delete-tutors',
            'approve-tutors',
            'reject-tutors',
            'view-dashboard',
        ]);

        // Enquiries Manager
        $enquiriesManager = Role::firstOrCreate(
            ['name' => 'enquiries_admin'],
            ['guard_name' => 'web']
        );
        $enquiriesManager->syncPermissions([
            'view-enquiries',
            'manage-enquiries',
            'respond-enquiries',
            'view-students',
            'view-tutors',
            'view-dashboard',
        ]);

        // Reviews Manager
        $reviewsManager = Role::firstOrCreate(
            ['name' => 'reviews_admin'],
            ['guard_name' => 'web']
        );
        $reviewsManager->syncPermissions([
            'view-reviews',
            'approve-reviews',
            'reject-reviews',
            'delete-reviews',
            'view-tutors',
            'view-students',
            'view-dashboard',
        ]);

        // Service Manager
        $serviceManager = Role::firstOrCreate(
            ['name' => 'service_admin'],
            ['guard_name' => 'web']
        );
        $serviceManager->syncPermissions([
            'view-services',
            'create-services',
            'edit-services',
            'delete-services',
            'view-orders',
            'manage-orders',
            'view-dashboard',
        ]);
    }
}
