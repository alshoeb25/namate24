<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;
use App\Models\CreditPackage;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminAndSampleSeeder extends Seeder
{
    public function run()
    {
        // Roles
        Role::firstOrCreate(['name'=>'admin']);
        Role::firstOrCreate(['name'=>'tutor']);
        Role::firstOrCreate(['name'=>'student']);

        // Admin
        $admin = User::firstOrCreate(['email'=>'admin@example.com'], [
            'name'=>'Admin',
            'password'=>Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin->wallet()->firstOrCreate([]);

        // Sample subjects
        $subjects = ['Mathematics','Physics','Chemistry','Biology','English'];
        foreach ($subjects as $s) Subject::firstOrCreate(['slug'=>\Str::slug($s)], ['name'=>$s]);

        // Credit packages
        CreditPackage::firstOrCreate(['credits'=>50], ['name'=>'Starter','price'=>499,'validity_days'=>90]);
        CreditPackage::firstOrCreate(['credits'=>200], ['name'=>'Pro','price'=>1499,'validity_days'=>365]);
    }
}