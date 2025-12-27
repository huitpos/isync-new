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
        // Define all permissions as a structured array
        $permissionsList = $this->getPermissionsList();
        
        // Get all permission names from the list
        $permissionNames = collect($permissionsList)->map(fn($p) => $p['name'])->toArray();
        
        // Delete permissions that are no longer in the list
        Permission::whereNotIn('name', $permissionNames)->delete();
        
        // Create or update permissions
        $this->syncPermissions($permissionsList);
        
        // Assign permissions to roles
        $this->assignRolePermissions();
    }

    /**
     * Define all permissions with their parent-child relationships
     */
    private function getPermissionsList(): array
    {
        return [
            // ADMIN LEVEL PERMISSIONS
            ['name' => 'Dashboard', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.dashboard'],
            
            // Clients
            ['name' => 'Clients', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clients.index', 'parent' => null],
            ['name' => 'Clients/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clients.show', 'parent' => 'Clients'],
            ['name' => 'Clients/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clients.create', 'parent' => 'Clients'],
            ['name' => 'Clients/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clients.edit', 'parent' => 'Clients'],
            
            // Clusters
            ['name' => 'Clusters', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clusters.index', 'parent' => null],
            ['name' => 'Clusters/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clusters.show', 'parent' => 'Clusters'],
            ['name' => 'Clusters/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clusters.create', 'parent' => 'Clusters'],
            ['name' => 'Clusters/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.clusters.edit', 'parent' => 'Clusters'],
            
            // Branches
            ['name' => 'Branches', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.branches.index', 'parent' => null],
            ['name' => 'Branches/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.branches.show', 'parent' => 'Branches'],
            ['name' => 'Branches/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.branches.create', 'parent' => 'Branches'],
            ['name' => 'Branches/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'route' => 'admin.branches.show', 'parent' => 'Branches'],
            ['name' => 'Branches/View/Machine Details', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Branches'],
            ['name' => 'Branches/View/Machine Details/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Branches'],
            ['name' => 'Branches/View/Machine Details/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Branches'],
            ['name' => 'Branches/View/Machined Details/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Branches'],
            ['name' => 'Branches/View/Machine Details/View/Device Logs', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Branches'],
            ['name' => 'Branches/View/Machine Details/View/Device Logs/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Branches'],
            ['name' => 'Branches/View/Machine Details/View/Device Logs/Delete', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Branches'],
            
            // Access Level
            ['name' => 'Access Level', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => null],
            ['name' => 'Access Level/Users', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Access Level'],
            ['name' => 'Access Level/Users/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Access Level'],
            ['name' => 'Access Level/Users/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Access Level'],
            ['name' => 'Access Level/Users/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Access Level'],
            ['name' => 'Access Level/User Role', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Access Level'],
            ['name' => 'Access Level/User Role/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Access Level'],
            ['name' => 'Access Level/User Role/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Access Level'],
            ['name' => 'Access Level/User Role/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'Access Level'],
            
            // POS Provider
            ['name' => 'POS Provider', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => null],
            ['name' => 'POS Provider/View', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'POS Provider'],
            ['name' => 'POS Provider/Add', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'POS Provider'],
            ['name' => 'POS Provider/Edit', 'guard_name' => 'web', 'level' => 'isync_admin', 'parent' => 'POS Provider'],
            
            // COMPANY LEVEL PERMISSIONS
            // Main Dashboard
            ['name' => 'Main Dashboard', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.dashboard', 'parent' => null],
            
            // Inventory
            ['name' => 'Inventory', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => null],
            ['name' => 'Inventory/Products', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.branch-inventory.index', 'parent' => 'Inventory'],
            ['name' => 'Inventory/Products/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.branch-inventory.show', 'parent' => 'Inventory/Products'],
            ['name' => 'Inventory/Product Disposal', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.product-disposals.index', 'parent' => 'Inventory'],
            ['name' => 'Inventory/Product Disposal/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.product-disposals.show', 'parent' => 'Inventory/Product Disposal'],
            ['name' => 'Inventory/Product Physical Count', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.product-physical-counts.index', 'parent' => 'Inventory'],
            ['name' => 'Inventory/Product Physical Count/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.product-physical-counts.show', 'parent' => 'Inventory/Product Physical Count'],
            
            // Procurement
            ['name' => 'Procurement', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => null],
            ['name' => 'Procurement/Purchase Requests', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.purchase-requests.index', 'parent' => 'Procurement'],
            ['name' => 'Procurement/Purchase Requests/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.purchase-requests.show', 'parent' => 'Procurement/Purchase Requests'],
            ['name' => 'Procurement/Purchase Orders', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.purchase-orders.index', 'parent' => 'Procurement'],
            ['name' => 'Procurement/Purchase Orders/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.purchase-orders.show', 'parent' => 'Procurement/Purchase Orders'],
            ['name' => 'Procurement/Purchase Deliveries', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.purchase-deliveries.index', 'parent' => 'Procurement'],
            ['name' => 'Procurement/Purchase Deliveries/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.purchase-deliveries.show', 'parent' => 'Procurement/Purchase Deliveries'],
            ['name' => 'Procurement/Stock Transfer Requests', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.stock-transfer-requests.index', 'parent' => 'Procurement'],
            ['name' => 'Procurement/Stock Transfer Requests/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.stock-transfer-requests.show', 'parent' => 'Procurement/Stock Transfer Requests'],
            
            // Company Access Level
            ['name' => 'Company Access Level', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => null],
            ['name' => 'Company Access Level/Users', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.users.index', 'parent' => 'Company Access Level'],
            ['name' => 'Company Access Level/Users/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.users.show', 'parent' => 'Company Access Level/Users'],
            ['name' => 'Company Access Level/Users/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.users.create', 'parent' => 'Company Access Level/Users'],
            ['name' => 'Company Access Level/Users/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.users.edit', 'parent' => 'Company Access Level/Users'],
            
            // Settings
            ['name' => 'Settings', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => null],
            ['name' => 'Settings/Products', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.products.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Products/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.products.show', 'parent' => 'Settings/Products'],
            ['name' => 'Settings/Products/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.products.create', 'parent' => 'Settings/Products'],
            ['name' => 'Settings/Products/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.products.edit', 'parent' => 'Settings/Products'],
            ['name' => 'Settings/Products/Import Products', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.products.index', 'parent' => 'Settings/Products'],
            ['name' => 'Settings/Products/Download Product Template', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.products.index', 'parent' => 'Settings/Products'],
            
            ['name' => 'Settings/Payment Types', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.payment-types.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Payment Types/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.payment-types.show', 'parent' => 'Settings/Payment Types'],
            ['name' => 'Settings/Payment Types/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.payment-types.create', 'parent' => 'Settings/Payment Types'],
            ['name' => 'Settings/Payment Types/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.payment-types.edit', 'parent' => 'Settings/Payment Types'],
            
            ['name' => 'Settings/Departments', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.departments.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Departments/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.departments.show', 'parent' => 'Settings/Departments'],
            ['name' => 'Settings/Departments/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.departments.create', 'parent' => 'Settings/Departments'],
            ['name' => 'Settings/Departments/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.departments.edit', 'parent' => 'Settings/Departments'],
            
            ['name' => 'Settings/Categories', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.categories.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Categories/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.categories.show', 'parent' => 'Settings/Categories'],
            ['name' => 'Settings/Categories/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.categories.create', 'parent' => 'Settings/Categories'],
            ['name' => 'Settings/Categories/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.categories.edit', 'parent' => 'Settings/Categories'],
            
            ['name' => 'Settings/Sub-Categories', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.subcategories.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Sub-Categories/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.subcategories.show', 'parent' => 'Settings/Sub-Categories'],
            ['name' => 'Settings/Sub-Categories/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.subcategories.create', 'parent' => 'Settings/Sub-Categories'],
            ['name' => 'Settings/Sub-Categories/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.subcategories.edit', 'parent' => 'Settings/Sub-Categories'],
            
            ['name' => 'Settings/Item Types', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.item-types.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Item Types/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.item-types.show', 'parent' => 'Settings/Item Types'],
            ['name' => 'Settings/Item Types/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.item-types.create', 'parent' => 'Settings/Item Types'],
            ['name' => 'Settings/Item Types/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.item-types.edit', 'parent' => 'Settings/Item Types'],
            
            ['name' => 'Settings/Unit of Measurements', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.unit-of-measurements.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Unit of Measurements/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.unit-of-measurements.show', 'parent' => 'Settings/Unit of Measurements'],
            ['name' => 'Settings/Unit of Measurements/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.unit-of-measurements.create', 'parent' => 'Settings/Unit of Measurements'],
            ['name' => 'Settings/Unit of Measurements/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.unit-of-measurements.edit', 'parent' => 'Settings/Unit of Measurements'],
            
            ['name' => 'Settings/Discount Types', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.discount-types.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Discount Types/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.discount-types.show', 'parent' => 'Settings/Discount Types'],
            ['name' => 'Settings/Discount Types/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.discount-types.create', 'parent' => 'Settings/Discount Types'],
            ['name' => 'Settings/Discount Types/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.discount-types.edit', 'parent' => 'Settings/Discount Types'],
            
            ['name' => 'Settings/Suppliers', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.suppliers.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Suppliers/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.suppliers.show', 'parent' => 'Settings/Suppliers'],
            ['name' => 'Settings/Suppliers/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.suppliers.create', 'parent' => 'Settings/Suppliers'],
            ['name' => 'Settings/Suppliers/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.suppliers.edit', 'parent' => 'Settings/Suppliers'],
            
            ['name' => 'Settings/Payment Terms', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.payment-terms.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Payment Terms/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.payment-terms.show', 'parent' => 'Settings/Payment Terms'],
            ['name' => 'Settings/Payment Terms/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.payment-terms.create', 'parent' => 'Settings/Payment Terms'],
            ['name' => 'Settings/Payment Terms/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.payment-terms.edit', 'parent' => 'Settings/Payment Terms'],
            
            ['name' => 'Settings/Supplier Terms', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.supplier-terms.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Supplier Terms/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.supplier-terms.show', 'parent' => 'Settings/Supplier Terms'],
            ['name' => 'Settings/Supplier Terms/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.supplier-terms.create', 'parent' => 'Settings/Supplier Terms'],
            ['name' => 'Settings/Supplier Terms/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.supplier-terms.edit', 'parent' => 'Settings/Supplier Terms'],
            
            ['name' => 'Settings/Product Disposal Reasons', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.product-disposal-reasons.index', 'parent' => 'Settings'],
            ['name' => 'Settings/Product Disposal Reasons/View', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.product-disposal-reasons.show', 'parent' => 'Settings/Product Disposal Reasons'],
            ['name' => 'Settings/Product Disposal Reasons/Add', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.product-disposal-reasons.create', 'parent' => 'Settings/Product Disposal Reasons'],
            ['name' => 'Settings/Product Disposal Reasons/Edit', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.product-disposal-reasons.edit', 'parent' => 'Settings/Product Disposal Reasons'],

            ['name' => 'Settings/Item Locations', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.item-locations.index', 'parent' => 'Settings'],

            ['name' => 'Settings/Change Price Reasons', 'guard_name' => 'web', 'level' => 'company_user', 'route' => 'company.change-price-reasons.index', 'parent' => 'Settings'],

            ['name' => 'Company Reports', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => null],

            ['name' => 'Company Reports/Inventory Reports', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports'],
            ['name' => 'Company Reports/Inventory Reports/Stock Card', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Inventory Reports', 'route' => 'company.reports.stock-card'],

            ['name' => 'Company Reports/Sales Reports', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports'],
            ['name' => 'Company Reports/Sales Reports/Sales Invoices Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.sales-invoices-report'],
            ['name' => 'Company Reports/Sales Reports/Sales Transaction Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.sales-transaction-report'],
            ['name' => 'Company Reports/Sales Reports/Void Transactions Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.void-transactions-report'],
            ['name' => 'Company Reports/Sales Reports/Vat Sales Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.vat-sales-report'],
            ['name' => 'Company Reports/Sales Reports/X Reading Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.x-reading-report'],
            ['name' => 'Company Reports/Sales Reports/Z Reading Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.z-reading-report'],
            ['name' => 'Company Reports/Sales Reports/Discounts Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.discounts-report'],
            ['name' => 'Company Reports/Sales Reports/Item Sales Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.item-sales-report'],
            ['name' => 'Company Reports/Sales Reports/Sales Summary Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.bir-sales-summary-report'],
            ['name' => 'Company Reports/Sales Reports/Senior Citizen Sales Book', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.bir-senior-citizen-sales-report'],
            ['name' => 'Company Reports/Sales Reports/Persons with Disability Sales Book', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.bir-pwd-sales-report'],
            ['name' => 'Company Reports/Sales Reports/National Athletes and Coaches Sales Book', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.bir-naac-sales-report'],
            ['name' => 'Company Reports/Sales Reports/Solo Parent Sales Book', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports/Sales Reports', 'route' => 'company.reports.bir-solo-parent-sales-report'],
            ['name' => 'Company Reports/Audit Trail Report', 'guard_name' => 'web', 'level' => 'company_user', 'parent' => 'Company Reports', 'route' => 'company.reports.audit-trail'],
            
            // BRANCH LEVEL PERMISSIONS
            // Branch Dashboard
            ['name' => 'Branch Dashboard', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.dashboard', 'parent' => null],
            ['name' => 'Branch Users', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.users.index', 'parent' => null],
            ['name' => 'Branch Delivery Locations', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.delivery-locations.index', 'parent' => null],
            ['name' => 'Branch Procurement', 'guard_name' => 'web', 'level' => 'branch_user', 'parent' => null],
            ['name' => 'Branch Procurement/Purchase Requests', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.purchase-requests.index', 'parent' => 'Branch Procurement'],
            ['name' => 'Branch Procurement/Purchase Orders', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.purchase-orders.index', 'parent' => 'Branch Procurement'],
            ['name' => 'Branch Procurement/Purchase Deliveries', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.purchase-deliveries.index', 'parent' => 'Branch Procurement'],
            ['name' => 'Branch Inventory', 'guard_name' => 'web', 'level' => 'branch_user', 'parent' => null],
            ['name' => 'Branch Inventory/Stock Transfer Requests', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.stock-transfer-requests.index', 'parent' => 'Branch Inventory'],
            ['name' => 'Branch Inventory/Stock Transfer Orders', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.stock-transfer-orders.index', 'parent' => 'Branch Inventory'],
            ['name' => 'Branch Inventory/Stock Transfer Deliveries', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.stock-transfer-deliveries.index', 'parent' => 'Branch Inventory'],
            ['name' => 'Branch Inventory/Product Physical Count', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.product-physical-counts.index', 'parent' => 'Branch Inventory'],
            ['name' => 'Branch Inventory/Product Disposals', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.product-disposals.index', 'parent' => 'Branch Inventory'],
            ['name' => 'Branch Products', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.products.index', 'parent' => null],
            ['name' => 'Branch Reports', 'guard_name' => 'web', 'level' => 'branch_user', 'parent' => null],
            ['name' => 'Branch Reports/Sales Invoices Report', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.sales-invoices-report', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/Sales Transaction Report', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.sales-transaction-report', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/Void Transaction Report', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.void-transactions-report', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/Vat Sales Report', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.vat-sales-report', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/X Reading Report', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.x-reading-report', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/Z Reading Report', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.z-reading-report', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/Discounts Report', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.discounts-report', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/Item Sales Report', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.item-sales-report', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/Stock Card', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.stock-card', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Reports/Audit Trail', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.reports.audit-trail', 'parent' => 'Branch Reports'],
            ['name' => 'Branch Customer Informations', 'guard_name' => 'web', 'level' => 'branch_user', 'route' => 'branch.charge-accounts.index', 'parent' => null],

            // POS
            ['name' => 'POS', 'guard_name' => 'web', 'level' => 'pos', 'parent' => null],
            ['name' => 'POS/Resume Transaction', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Resume Transaction/Resume', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS/Resume Transaction'],
            ['name' => 'POS/Backout', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Orders', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/View Receipt', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/View Receipt/Reprint', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS/View Receipt'],
            ['name' => 'POS/View Receipt/Void', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS/View Receipt'],
            ['name' => 'POS/AR Redeeming', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Cut Off', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Cut Off/X Reading', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS/Cut Off'],
            ['name' => 'POS/Cut Off/Z Reading', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS/Cut Off'],
            ['name' => 'POS/Payout', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Safekeeping', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Spot Audit', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Sync Data', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Upload Server', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Backup', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Settings', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Payment', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Discount', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Item Void', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Print', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Open Drawer', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Item Select', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Item Select/Update Price', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS/Item Select'],
            ['name' => 'POS/Item Select/Update Qty', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS/Item Select'],
            ['name' => 'POS/Item Select/Return Item', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS/Item Select'],
            ['name' => 'POS/Clear Transaction', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Pause Transaction', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/View Products', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Search Product', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/Scan Barcode', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
            ['name' => 'POS/View Departments', 'guard_name' => 'web', 'level' => 'pos', 'parent' => 'POS'],
        ];
    }

    /**
     * Sync permissions to database
     */
    private function syncPermissions(array $permissionsList): void
    {
        // Cache for parent permissions to avoid repeated queries
        $permissionCache = [];
        
        foreach ($permissionsList as $permissionData) {
            // Check if permission already exists
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => $permissionData['guard_name']],
                [
                    'level' => $permissionData['level'] ?? null,
                    'route' => $permissionData['route'] ?? null,
                ]
            );
            
            // Update route and level if they differ
            if (isset($permissionData['route']) && $permission->route !== $permissionData['route']) {
                $permission->update(['route' => $permissionData['route']]);
            }
            if (isset($permissionData['level']) && $permission->level !== $permissionData['level']) {
                $permission->update(['level' => $permissionData['level']]);
            }
            
            // Handle parent relationship
            if (!empty($permissionData['parent'])) {
                // Check cache first, then query
                if (!isset($permissionCache[$permissionData['parent']])) {
                    $permissionCache[$permissionData['parent']] = Permission::where('name', $permissionData['parent'])->first();
                }
                
                $parent = $permissionCache[$permissionData['parent']];
                if ($parent && $permission->parent_id !== $parent->id) {
                    $permission->update(['parent_id' => $parent->id]);
                }
            } else {
                // Ensure parent_id is null for top-level permissions
                if ($permission->parent_id !== null) {
                    $permission->update(['parent_id' => null]);
                }
            }
        }
    }

    /**
     * Assign permissions to roles
     */
    private function assignRolePermissions(): void
    {
        $role = Role::findByName('company_admin');
        if ($role) {
            $permissions = Permission::where('level', 'company_user')->pluck('id');
            $role->syncPermissions($permissions);
        }

        $role = Role::findByName('branch_user');
        if ($role) {
            $permissions = Permission::where('level', 'branch_user')->pluck('id');
            $role->syncPermissions($permissions);
        }
    }
}
