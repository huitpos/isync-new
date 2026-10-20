<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            ['name' => 'Accounting', 'parent' => null, 'level' => 'company_user', 'route' => null],
            ['name' => 'Accounting/Chart of Accounts', 'parent' => 'Accounting', 'level' => 'company_user', 'route' => 'company.accounts.index'],
            ['name' => 'Accounting/Account Mapping', 'parent' => 'Accounting', 'level' => 'company_user', 'route' => 'company.account-mappings.edit'],
            ['name' => 'Accounting/Journals', 'parent' => 'Accounting', 'level' => 'company_user', 'route' => 'company.journals.index'],
            ['name' => 'Accounting/Post Sales', 'parent' => 'Accounting', 'level' => 'company_user', 'route' => 'company.sales-posting.index'],
            ['name' => 'Accounting/Fiscal Periods', 'parent' => 'Accounting', 'level' => 'company_user', 'route' => 'company.fiscal-periods.index'],
            ['name' => 'Accounting/Reports', 'parent' => 'Accounting', 'level' => 'company_user', 'route' => null],
            ['name' => 'Accounting/Reports/Account Ledger', 'parent' => 'Accounting/Reports', 'level' => 'company_user', 'route' => 'company.gl.ledger'],
            ['name' => 'Accounting/Reports/Trial Balance', 'parent' => 'Accounting/Reports', 'level' => 'company_user', 'route' => 'company.gl.trial-balance'],
            ['name' => 'Accounting/Reports/Income Statement', 'parent' => 'Accounting/Reports', 'level' => 'company_user', 'route' => 'company.gl.income-statement'],
            ['name' => 'Accounting/Reports/Balance Sheet', 'parent' => 'Accounting/Reports', 'level' => 'company_user', 'route' => 'company.gl.balance-sheet'],
        ];

        $created = [];
        foreach ($permissions as $data) {
            $parentId = null;
            if ($data['parent']) {
                $parentId = Permission::where('name', $data['parent'])->where('guard_name', 'web')->value('id');
            }

            $created[] = Permission::firstOrCreate(
                ['name' => $data['name'], 'guard_name' => 'web'],
                ['level' => $data['level'], 'route' => $data['route'], 'parent_id' => $parentId]
            );
        }

        $role = Role::where('name', 'company_admin')->where('guard_name', 'web')->first();
        if ($role) {
            $role->givePermissionTo($created);
        }
    }

    public function down(): void
    {
        Permission::where('guard_name', 'web')->where('name', 'like', 'Accounting%')->delete();
    }
};
