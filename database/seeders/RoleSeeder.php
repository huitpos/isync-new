<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $companyAdminRole = Role::create(['name' => 'company_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'branch_user', 'guard_name' => 'web']);

        $allPermission = Permission::create(['name' => 'all', 'guard_name' => 'web']);

        $superAdminRole->givePermissionTo('all');

        // Create a user and assign the 'super_admin' role
        $user = User::create([
            'name' => 'Super Admin Name',
            'email' => 'admin@isync.ph',
            'password' => bcrypt('qwe123'), // Replace 'password' with the desired password
        ]);

        $user->assignRole('super_admin');
    }
}