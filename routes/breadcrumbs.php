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