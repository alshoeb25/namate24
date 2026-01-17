<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CheckReferralPermissions extends Command
{
    protected $signature = 'referral:check-permissions';
    protected $description = 'Check referral permissions and role setup';

    public function handle()
    {
        $this->info('Referral Permissions:');
        Permission::where('name', 'LIKE', '%referral%')
            ->pluck('name')
            ->each(fn($p) => $this->line("  ✓ $p"));

        $this->newLine();
        $this->info('Referral Admin Role Permissions:');
        
        $role = Role::where('name', 'referral_admin')->first();
        if ($role) {
            $role->permissions()
                ->pluck('name')
                ->each(fn($p) => $this->line("  ✓ $p"));
        } else {
            $this->error('  ✗ Role not found');
        }

        $this->newLine();
        $this->info('All Referral Admin Roles:');
        Role::where('name', 'LIKE', '%referral%')
            ->pluck('name')
            ->each(fn($r) => $this->line("  ✓ $r"));
    }
}
