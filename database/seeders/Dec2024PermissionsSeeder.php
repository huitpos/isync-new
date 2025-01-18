<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class Dec2024PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainDashboard = Permission::where('name', 'Main Dashboard')->first();
        Permission::where('parent_id', '=', $mainDashboard->id)->delete();

        //get permission with name POS/View Departments
        $lastPermission = Permission::where('name', 'POS/View Departments')->first();

        //delete all permission greater than the last permission
        Permission::where('id', '>', $lastPermission->id)->delete();

        $settings = Permission::where('name', 'Settings')->first();
            $itemLocations = Permission::create(['name' => 'Settings/Item Locations', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $settings->id, 'route' => 'company.item-locations.index']);
                Permission::create(['name' => 'Settings/Item Locations/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $itemLocations->id, 'route' => 'company.item-locations.show']);
                Permission::create(['name' => 'Settings/Item Locations/Add', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $itemLocations->id, 'route' => 'company.item-locations.create']);
                Permission::create(['name' => 'Settings/Item Locations/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $itemLocations->id, 'route' => 'company.item-locations.edit']);

            $changePriceReasons = Permission::create(['name' => 'Settings/Change Price Reasons', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $settings->id, 'route' => 'company.change-price-reasons.index']);
                Permission::create(['name' => 'Settings/Change Price Reasons/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $changePriceReasons->id, 'route' => 'company.change-price-reasons.show']);
                Permission::create(['name' => 'Settings/Change Price Reasons/Add', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $changePriceReasons->id, 'route' => 'company.change-price-reasons.create']);
                Permission::create(['name' => 'Settings/Change Price Reasons/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $changePriceReasons->id, 'route' => 'company.change-price-reasons.edit']);

        $companyReports = Permission::create(['name' => 'Company Reports', 'guard_name' => 'web', 'level' => 'company_user']);
    }
}