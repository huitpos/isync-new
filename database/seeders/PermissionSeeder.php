<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //remove all permission that is not id = 1
        // Permission::where('id', '!=', 1)->delete();
        Permission::query()->delete();

        //ADMIN
        //dashboard
        Permission::create(['name' => 'Dashboard', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.dashboard']);

        //clients
        $clients = Permission::create(['name' => 'Clients', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clients.index']);
            Permission::create(['name' => 'Clients/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $clients->id, 'route' => 'admin.clients.show']);
            Permission::create(['name' => 'Clients/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $clients->id, 'route' => 'admin.clients.create']);
            Permission::create(['name' => 'Clients/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $clients->id, 'route' => 'admin.clients.edit']);

        //clusters
        $clusters = Permission::create(['name' => 'Clusters', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clusters.index']);
            Permission::create(['name' => 'Clusters/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $clusters->id, 'route' => 'admin.clusters.show']);
            Permission::create(['name' => 'Clusters/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $clusters->id, 'route' => 'admin.clusters.create']);
            Permission::create(['name' => 'Clusters/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $clusters->id, 'route' => 'admin.clusters.edit']);

        //branches
        $branches = Permission::create(['name' => 'Branches', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.branches.index']);
            Permission::create(['name' => 'Branches/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id, 'route' => 'admin.branches.show']);
            Permission::create(['name' => 'Branches/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id, 'route' => 'admin.branches.create']);
            Permission::create(['name' => 'Branches/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id, 'route' => 'admin.branches.show']);
                //branch child
                Permission::create(['name' => 'Branches/View/Machine Details', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id]);
                Permission::create(['name' => 'Branches/View/Machine Details/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id]);
                Permission::create(['name' => 'Branches/View/Machine Details/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id]);
                Permission::create(['name' => 'Branches/View/Machined Details/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id]);
                    //branch grandchild
                    Permission::create(['name' => 'Branches/View/Machine Details/View/Device Logs', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id]);
                        //branch great-grandchild
                        Permission::create(['name' => 'Branches/View/Machine Details/View/Device Logs/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id]);
                        Permission::create(['name' => 'Branches/View/Machine Details/View/Device Logs/Delete', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $branches->id]);


        //access level
        $accessLevel = Permission::create(['name' => 'Access Level', 'guard_name' => 'web', 'level' => 'isync_admin']);
            Permission::create(['name' => 'Access Level/Users', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $accessLevel->id]);
            Permission::create(['name' => 'Access Level/Users/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $accessLevel->id]);
            Permission::create(['name' => 'Access Level/Users/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $accessLevel->id]);
            Permission::create(['name' => 'Access Level/Users/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $accessLevel->id]);
            Permission::create(['name' => 'Access Level/User Role', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $accessLevel->id]);
            Permission::create(['name' => 'Access Level/User Role/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $accessLevel->id]);
            Permission::create(['name' => 'Access Level/User Role/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $accessLevel->id]);
            Permission::create(['name' => 'Access Level/User Role/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $accessLevel->id]);

        //POS provider
        $posProvider = Permission::create(['name' => 'POS Provider', 'guard_name' => 'web', 'level' => 'isync_admin']);
            Permission::create(['name' => 'POS Provider/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $posProvider->id]);
            Permission::create(['name' => 'POS Provider/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $posProvider->id]);
            Permission::create(['name' => 'POS Provider/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent_id' => $posProvider->id]);

        //COMPANY LEVEL
        $mainDashboard = Permission::create(['name' => 'Main Dashboard', 'guard_name' => 'web', 'level' => 'company_user']);
            Permission::create(['name' => 'Main Dashboard/Transaction Count', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $mainDashboard->id, 'route' => 'company.dashboard']);
            Permission::create(['name' => 'Main Dashboard/Total Net Amount', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $mainDashboard->id, 'route' => 'company.dashboard']);
            Permission::create(['name' => 'Main Dashboard/Total Cost Amount', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $mainDashboard->id, 'route' => 'company.dashboard']);
            Permission::create(['name' => 'Main Dashboard/Profit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $mainDashboard->id, 'route' => 'company.dashboard']);
            Permission::create(['name' => 'Main Dashboard/Transaction/Completed Transactions', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $mainDashboard->id, 'route' => 'company.dashboard']);
            Permission::create(['name' => 'Main Dashboard/Transaction/Pending Transactions', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $mainDashboard->id, 'route' => 'company.dashboard']);
            Permission::create(['name' => 'Main Dashboard/Transaction/Voided Transactions', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $mainDashboard->id, 'route' => 'company.dashboard']);

        //inventory
        $inventory = Permission::create(['name' => 'Inventory', 'guard_name' => 'web', 'level' => 'company_user']);
            $inventoryProducts = Permission::create(['name' => 'Inventory/Products', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $inventory->id, 'route' => 'company.branch-inventory.index']);
                Permission::create(['name' => 'Inventory/Products/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $inventoryProducts->id, 'route' => 'company.branch-inventory.show']);

            $disposal = Permission::create(['name' => 'Inventory/Product Disposal', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $inventory->id, 'route' => 'company.product-disposals.index']);
                Permission::create(['name' => 'Inventory/Product Disposal/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $disposal->id, 'route' => 'company.product-disposals.show']);

            $pcount = Permission::create(['name' => 'Inventory/Product Physical Count', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $inventory->id, 'route' => 'company.product-physical-counts.index']);
                Permission::create(['name' => 'Inventory/Product Physical Count/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $pcount->id, 'route' => 'company.product-physical-counts.show']);

        //procurement
        $procurement = Permission::create(['name' => 'Procurement', 'guard_name' => 'web', 'level' => 'company_user']);
            $pr = Permission::create(['name' => 'Procurement/Purchase Requests', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $procurement->id, 'route' => 'company.purchase-requests.index']);
                Permission::create(['name' => 'Procurement/Purchase Requests/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $pr->id, 'route' => 'company.purchase-requests.show']);
            $po = Permission::create(['name' => 'Procurement/Purchase Orders', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $procurement->id, 'route' => 'company.purchase-orders.index']);
                Permission::create(['name' => 'Procurement/Purchase Orders/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $po->id, 'route' => 'company.purchase-orders.show']);
            $pd = Permission::create(['name' => 'Procurement/Purchase Deliveries', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $procurement->id, 'route' => 'company.purchase-deliveries.index']);
                Permission::create(['name' => 'Procurement/Purchase Deliveries/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $pd->id, 'route' => 'company.purchase-deliveries.show']);
            $str = Permission::create(['name' => 'Procurement/Stock Transfer Requests', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $procurement->id, 'route' => 'company.stock-transfer-requests.index']);
                Permission::create(['name' => 'Procurement/Stock Transfer Requests/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $str->id, 'route' => 'company.stock-transfer-requests.show']);

        //Access Level
        $accessLevel = Permission::create(['name' => 'Company Access Level', 'guard_name' => 'web', 'level' => 'company_user']);
            $users = Permission::create(['name' => 'Company Access Level/Users', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $accessLevel->id, 'route' => 'company.users.index']);
                Permission::create(['name' => 'Company Access Level/Users/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $users->id, 'route' => 'company.users.show']);
                Permission::create(['name' => 'Company Access Level/Users/Add', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $users->id, 'route' => 'company.users.create']);
                Permission::create(['name' => 'Company Access Level/Users/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $users->id, 'route' => 'company.users.edit']);
            $role = Permission::create(['name' => 'Company Access Level/Role', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $accessLevel->id, 'route' => 'company.roles.index']);
                Permission::create(['name' => 'Company Access Level/Role/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $role->id, 'route' => 'company.roles.show']);
                Permission::create(['name' => 'Company Access Level/Role/Add', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $role->id, 'route' => 'company.roles.create']);
                Permission::create(['name' => 'Company Access Level/Role/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $role->id, 'route' => 'company.roles.edit']);

        //settings
        $settings = Permission::create(['name' => 'Settings', 'guard_name' => 'web', 'level' => 'company_user']);
            $products = Permission::create(['name' => 'Settings/Products', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $settings->id, 'route' => 'company.products.index']);
                Permission::create(['name' => 'Settings/Products/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $products->id, 'route' => 'company.products.show']);
                Permission::create(['name' => 'Settings/Products/Add', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $products->id, 'route' => 'company.products.create']);
                Permission::create(['name' => 'Settings/Products/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $products->id, 'route' => 'company.products.edit']);
                Permission::create(['name' => 'Settings/Products/Import Products', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $products->id, 'route' => 'company.products.index']);
                Permission::create(['name' => 'Settings/Products/Download Product Template', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $products->id, 'route' => 'company.products.index']);

            $paymentType = Permission::create(['name' => 'Settings/Payment Types', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $settings->id, 'route' => 'company.payment-types.index']);
                Permission::create(['name' => 'Settings/Payment Types/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $paymentType->id, 'route' => 'company.payment-types.show']);
                Permission::create(['name' => 'Settings/Payment Types/Add', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $paymentType->id, 'route' => 'company.payment-types.create']);
                Permission::create(['name' => 'Settings/Payment Types/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $paymentType->id, 'route' => 'company.payment-types.edit']);

            $departments = Permission::create(['name' => 'Settings/Departments', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $settings->id, 'route' => 'company.departments.index']);
                Permission::create(['name' => 'Settings/Departments/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $departments->id, 'route' => 'company.departments.show']);
                Permission::create(['name' => 'Settings/Departments/Add', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $departments->id, 'route' => 'company.departments.create']);
                Permission::create(['name' => 'Settings/Departments/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $departments->id, 'route' => 'company.departments.edit']);

            $categories = Permission::create(['name' => 'Settings/Categories', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $settings->id, 'route' => 'company.categories.index']);
                Permission::create(['name' => 'Settings/Categories/View', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $categories->id, 'route' => 'company.categories.show']);
                Permission::create(['name' => 'Settings/Categories/Add', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $categories->id, 'route' => 'company.categories.create']);
                Permission::create(['name' => 'Settings/Categories/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'parent_id' => $categories->id, 'route' => 'company.categories.edit']);

        //BRANCH
        //Dashboard
        $dashboard = Permission::create(['name' => 'Branch Dashboard', 'guard_name' => 'web', 'level' => 'branch_user']);
            Permission::create(['name' => 'Branch Dashboard/Transaction Count', 'guard_name' => 'web', 'level' => 'branch_user', 'parent_id' => $dashboard->id, 'route' => 'branch.dashboard']);
            Permission::create(['name' => 'Branch Dashboard/Total Net Amount', 'guard_name' => 'web', 'level' => 'branch_user', 'parent_id' => $dashboard->id, 'route' => 'branch.dashboard']);
            Permission::create(['name' => 'Branch Dashboard/Total Cost Amount', 'guard_name' => 'web', 'level' => 'branch_user', 'parent_id' => $dashboard->id, 'route' => 'branch.dashboard']);
            Permission::create(['name' => 'Branch Dashboard/Profit', 'guard_name' => 'web', 'level' => 'branch_user', 'parent_id' => $dashboard->id, 'route' => 'branch.dashboard']);
            Permission::create(['name' => 'Branch Dashboard/Transaction', 'guard_name' => 'web', 'level' => 'branch_user', 'parent_id' => $dashboard->id, 'route' => 'branch.dashboard']);
            Permission::create(['name' => 'Branch Dashboard/Transaction/Completed Transactions', 'guard_name' => 'web', 'level' => 'branch_user', 'parent_id' => $dashboard->id, 'route' => 'branch.dashboard']);
            Permission::create(['name' => 'Branch Dashboard/Transaction/Pending Transactions', 'guard_name' => 'web', 'level' => 'branch_user', 'parent_id' => $dashboard->id, 'route' => 'branch.dashboard']);
            Permission::create(['name' => 'Branch Dashboard/Transaction/Voided Transactions', 'guard_name' => 'web', 'level' => 'branch_user', 'parent_id' => $dashboard->id, 'route' => 'branch.dashboard']);

        //POS
        $pos = Permission::create(['name' => 'POS', 'guard_name' => 'web', 'level' => 'pos']);
            Permission::create(['name' => 'POS/Resume Transaction', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Resume Transaction/Resume', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Backout', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Orders', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/View Receipt', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/View Receipt/Reprint', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/View Receipt/Void', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/AR Redeeming', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Cut Off', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Cut Off/X Reading', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Cut Off/Z Reading', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Payout', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Safekeeping', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Spot Audit', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Sync Data', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Upload Server', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Backup', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Settings', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Payment', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Discount', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Item Void', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Print', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Open Drawer', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Item Select', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Item Select/Update Price', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Item Select/Update Qty', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Item Select/Return Item', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Clear Transaction', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Pause Transaction', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/View Products', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Search Product', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/Scan Barcode', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);
            Permission::create(['name' => 'POS/View Departments', 'guard_name' => 'web', 'level' => 'pos', 'parent_id' => $pos->id]);

        $role = Role::findByName('company_admin');
        $permissions = Permission::where('level', 'company_user')->pluck('id');
        $role->syncPermissions($permissions);

        $role = Role::findByName('branch_user');
        $permissions = Permission::where('level', 'branch_user')->pluck('id');
        $role->syncPermissions($permissions);
    }
}