<?php

use App\Models\User;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

//admin
Breadcrumbs::for('admin.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Admin', route('admin.dashboard'));
});

//clients
Breadcrumbs::for('admin.clients.index', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Clients', route('admin.clients.index'));
});

Breadcrumbs::for('admin.clients.create', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.clients.index');
    $trail->push('Create');
});

Breadcrumbs::for('admin.clients.show', function (BreadcrumbTrail $trail, $clientName) {
    $trail->parent('admin.clients.index');
    $trail->push(ucfirst($clientName));
});

Breadcrumbs::for('admin.clients.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.clients.index');
    $trail->push('Edit');
});

//clusters
Breadcrumbs::for('admin.clusters.index', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Clusters', route('admin.clusters.index'));
});

Breadcrumbs::for('admin.clusters.create', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.clusters.index');
    $trail->push('Create');
});

Breadcrumbs::for('admin.clusters.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.clusters.index');
    $trail->push('Edit');
});

Breadcrumbs::for('admin.clusters.show', function (BreadcrumbTrail $trail, $clusterName) {
    $trail->parent('admin.clusters.index');
    $trail->push(ucfirst($clusterName));
});

//branch
Breadcrumbs::for('admin.branches.index', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Branches', route('admin.branches.index'));
});

Breadcrumbs::for('admin.branches.create', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.branches.index');
    $trail->push('Create');
});

Breadcrumbs::for('admin.branches.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.branches.index');
    $trail->push('Edit');
});

Breadcrumbs::for('admin.branches.show', function (BreadcrumbTrail $trail, $branch) {
    $trail->parent('admin.branches.index');
    $trail->push(ucfirst($branch->name), route('admin.branches.show', $branch));
});

//machines
Breadcrumbs::for('admin.machines.create', function (BreadcrumbTrail $trail, $branchName) {
    $trail->parent('admin.branches.show', $branchName);
    $trail->push('Machines');
    $trail->push('Create');
});

//company dashboard
Breadcrumbs::for('company.dashboard', function (BreadcrumbTrail $trail, $company) {
    $trail->push(ucfirst($company->trade_name), route('company.dashboard', ['companySlug' => $company->slug]));
});

//departments
Breadcrumbs::for('company.departments.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Departments', route('company.departments.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.departments.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.departments.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.departments.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.departments.index', $company);
    $trail->push('Edit');
});

//suppliers
Breadcrumbs::for('company.suppliers.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Suppliers', route('company.suppliers.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.suppliers.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.suppliers.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.suppliers.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.suppliers.index', $company);
    $trail->push('Edit');
});

Breadcrumbs::for('company.suppliers.show', function (BreadcrumbTrail $trail, $company, $supplier) {
    $trail->parent('company.suppliers.index', $company);
    $trail->push(ucfirst($supplier->name));
});

//products
Breadcrumbs::for('company.products.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Products', route('company.products.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.products.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.products.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('admin.products.show', function (BreadcrumbTrail $trail, $company, $product) {
    $trail->parent('company.products.index', $company);
    $trail->push($product->name);
});

//categories
Breadcrumbs::for('company.categories.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Categories', route('company.categories.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.categories.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.categories.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.categories.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.categories.index', $company);
    $trail->push('Edit');
});

Breadcrumbs::for('company.categories.show', function (BreadcrumbTrail $trail, $company, $category) {
    $trail->parent('company.categories.index', $company);
    $trail->push(ucfirst($category->name));
});

//subcategories
Breadcrumbs::for('company.subcategories.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Subcategories', route('company.subcategories.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.subcategories.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.subcategories.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.subcategories.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.subcategories.index', $company);
    $trail->push('Edit');
});

Breadcrumbs::for('company.subcategories.show', function (BreadcrumbTrail $trail, $company, $subcategory) {
    $trail->parent('company.subcategories.index', $company);
    $trail->push(ucfirst($subcategory->name));
});

//discount types
Breadcrumbs::for('company.discountTypes.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Discount Types', route('company.discount-types.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.discountTypes.show', function (BreadcrumbTrail $trail, $company, $discountType) {
    $trail->parent('company.discountTypes.index', $company);
    $trail->push(ucfirst($discountType->company_name));
});

Breadcrumbs::for('company.discountTypes.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.discountTypes.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.discountTypes.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.discountTypes.index', $company);
    $trail->push('Edit');
});

//charge accounts
Breadcrumbs::for('company.chargeAccounts.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Charge Accounts', route('company.charge-accounts.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.chargeAccounts.show', function (BreadcrumbTrail $trail, $company, $chargeAccount) {
    $trail->parent('company.chargeAccounts.index', $company);
    $trail->push(ucfirst($chargeAccount->name));
});

Breadcrumbs::for('company.chargeAccounts.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.chargeAccounts.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.chargeAccounts.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.chargeAccounts.index', $company);
    $trail->push('Edit');
});

//payment types
Breadcrumbs::for('company.paymentTypes.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Payment Types', route('company.payment-types.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.paymentTypes.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.paymentTypes.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.paymentTypes.show', function (BreadcrumbTrail $trail, $company, $paymentType) {
    $trail->parent('company.paymentTypes.index', $company);
    $trail->push(ucfirst($paymentType->name));
});

//uom
Breadcrumbs::for('company.uom.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Unit Of Measurements', route('company.unit-of-measurements.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.uom.show', function (BreadcrumbTrail $trail, $company, $uom) {
    $trail->parent('company.uom.index', $company);
    $trail->push(ucfirst($uom->name));
});

Breadcrumbs::for('company.uom.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.uom.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.uom.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.uom.index', $company);
    $trail->push('Edit');
});

//item types
Breadcrumbs::for('company.itemTypes.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Item Types', route('company.item-types.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.itemTypes.show', function (BreadcrumbTrail $trail, $company, $itemType) {
    $trail->parent('company.itemTypes.index', $company);
    $trail->push(ucfirst($itemType->name));
});

Breadcrumbs::for('company.itemTypes.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.itemTypes.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.itemTypes.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.itemTypes.index', $company);
    $trail->push('Edit');
});

//users
Breadcrumbs::for('company.users.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Users', route('company.users.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.users.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.users.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.users.edit', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.users.index', $company);
    $trail->push('Edit');
});

//company reports
Breadcrumbs::for('company.reports.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Reports');
});

Breadcrumbs::for('company.reports.viewTransaction', function (BreadcrumbTrail $trail, $company, $transaction) {
    $trail->parent('company.reports.index', $company);
    $trail->push('Transaction');
    $trail->push($transaction->id);
});

Breadcrumbs::for('company.purchaseRequests.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Purchase Requests', route('company.purchase-requests.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.purchaseRequests.show', function (BreadcrumbTrail $trail, $company, $pr) {
    $trail->parent('company.purchaseRequests.index', $company);
    $trail->push($pr->pr_number);
});

Breadcrumbs::for('company.purchaseOrders.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Purchase Orders', route('company.purchase-orders.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.purchaseOrders.show', function (BreadcrumbTrail $trail, $company, $po) {
    $trail->parent('company.purchaseOrders.index', $company);
    $trail->push($po->po_number);
});

Breadcrumbs::for('company.purchaseDeliveries.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Purchase Deliveries', route('company.purchase-orders.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.purchaseDeliveries.show', function (BreadcrumbTrail $trail, $company, $pd) {
    $trail->parent('company.purchaseDeliveries.index', $company);
    $trail->push($pd->pd_number);
});

Breadcrumbs::for('company.paymentTerms.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Payment Terms', route('company.payment-terms.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.paymentTerms.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.paymentTerms.index', $company);
    $trail->push('Create');
});

Breadcrumbs::for('company.supplierTerms.index', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.dashboard', $company);
    $trail->push('Supplier Terms', route('company.supplier-terms.index', ['companySlug' => $company->slug]));
});

Breadcrumbs::for('company.supplierTerms.create', function (BreadcrumbTrail $trail, $company) {
    $trail->parent('company.supplierTerms.index', $company);
    $trail->push('Create');
});




//branch dashboard
Breadcrumbs::for('branch.dashboard', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('company.dashboard', $company);
    $trail->push(ucfirst($branch->name), route('branch.dashboard', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
});

//branch reports
Breadcrumbs::for('branch.reports.index', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('company.dashboard', $company);
    $trail->push(ucfirst($branch->name), route('branch.dashboard', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
});

Breadcrumbs::for('branch.reports.viewTransaction', function (BreadcrumbTrail $trail, $company, $branch, $transaction) {
    $trail->parent('branch.reports.index', $company, $branch);
    $trail->push('Transaction');
    $trail->push($transaction->id);
});

// branch users
Breadcrumbs::for('branch.users.index', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('company.dashboard', $company);
    $trail->push(ucfirst($branch->name), route('branch.dashboard', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
    $trail->push('Users', route('branch.users.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
});

Breadcrumbs::for('branch.users.create', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('branch.users.index', $company, $branch);
    $trail->push('Create');
});

Breadcrumbs::for('branch.users.edit', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('branch.users.index', $company, $branch);
    $trail->push('Edit');
});

Breadcrumbs::for('branch.purchaseRequests.index', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('company.dashboard', $company);
    $trail->push(ucfirst($branch->name), route('branch.dashboard', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
    $trail->push('Purchase Requests', route('branch.purchase-requests.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
});

Breadcrumbs::for('branch.purchaseRequests.create', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('branch.purchaseRequests.index', $company, $branch);
    $trail->push('Create');
});

Breadcrumbs::for('branch.purchaseRequests.show', function (BreadcrumbTrail $trail, $company, $branch, $pr) {
    $trail->parent('branch.purchaseRequests.index', $company, $branch);
    $trail->push($pr->pr_number);
});

Breadcrumbs::for('branch.purchaseOrders.index', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('company.dashboard', $company);
    $trail->push(ucfirst($branch->name), route('branch.dashboard', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
    $trail->push('Purchase Orders', route('branch.purchase-orders.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
});

Breadcrumbs::for('branch.purchaseOrders.show', function (BreadcrumbTrail $trail, $company, $branch, $po) {
    $trail->parent('branch.purchaseOrders.index', $company, $branch);
    $trail->push($po->po_number);
});

Breadcrumbs::for('branch.deliveryLocations.index', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('company.dashboard', $company);
    $trail->push(ucfirst($branch->name), route('branch.dashboard', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
    $trail->push('Delivery Locations', route('branch.delivery-locations.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));
});

Breadcrumbs::for('branch.deliveryLocations.create', function (BreadcrumbTrail $trail, $company, $branch) {
    $trail->parent('branch.deliveryLocations.index', $company, $branch);
    $trail->push('Create');
});