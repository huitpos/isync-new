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
    $trail->push(ucfirst($company->company_name), route('company.dashboard', ['companySlug' => $company->slug]));
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