<?php

namespace App\Repositories\Interfaces;

use App\Models\Department;
use Illuminate\Support\Collection;

interface DepartmentRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Department;
    function findOrFail(String $id): ?Department;
    function create(array $attributes): Department;
    function update(String $id, array $attributes): Bool;
    function syncSuppliers(String $id, array $attributes): array;
    function delete(String $id): Bool;
}
