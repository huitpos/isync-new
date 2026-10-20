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
            ['name' => 'Procurement/Supplier Invoices', 'parent' => 'Procurement', 'level' => 'company_user', 'route' => 'company.supplier-invoices.index'],
            ['name' => 'Procurement/Debit Memos', 'parent' => 'Procurement', 'level' => 'company_user', 'route' => 'company.debit-memos.index'],
            ['name' => 'Inventory/Low Stock', 'parent' => 'Inventory', 'level' => 'company_user', 'route' => 'company.inventory.low-stock'],
            ['name' => 'Inventory/Valuation', 'parent' => 'Inventory', 'level' => 'company_user', 'route' => 'company.inventory.valuation'],
            ['name' => 'Branch Procurement/Purchase Returns', 'parent' => 'Branch Procurement', 'level' => 'branch_user', 'route' => 'branch.purchase-returns.index'],
        ];

        foreach ($permissions as $data) {
            $parent = Permission::where('name', $data['parent'])->where('guard_name', 'web')->first();
            $permission = Permission::firstOrCreate(
                ['name' => $data['name'], 'guard_name' => 'web'],
                ['level' => $data['level'], 'route' => $data['route'], 'parent_id' => $parent?->id]
            );

            Role::whereHas('permissions', function ($query) use ($data) {
                $query->where('name', $data['parent']);
            })->each(function (Role $role) use ($permission) {
                $role->givePermissionTo($permission);
            });
        }

        $reportNames = [
            'Company Reports/Sales Reports/Z Reading Report',
            'Company Reports/Sales Reports/Discounts Report',
            'Company Reports/Sales Reports/Item Sales Report',
            'Company Reports/Sales Reports/Sales Summary Report',
            'Company Reports/Sales Reports/Senior Citizen Sales Book',
            'Company Reports/Sales Reports/Persons with Disability Sales Book',
            'Company Reports/Sales Reports/National Athletes and Coaches Sales Book',
            'Company Reports/Sales Reports/Solo Parent Sales Book',
        ];

        Role::whereHas('permissions', function ($query) {
            $query->where('name', 'Company Reports');
        })->each(function (Role $role) use ($reportNames) {
            foreach ($reportNames as $name) {
                $permission = Permission::where('name', $name)->where('guard_name', 'web')->first();
                if ($permission) {
                    $role->givePermissionTo($permission);
                }
            }
        });
    }

    public function down(): void
    {
    }
};
