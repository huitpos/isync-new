<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Category;
    function findOrFail(String $id): ?Category;
    function create(array $attributes): Category;
    function update(String $id, array $attributes): Bool;
    function syncSuppliers(String $id, array $attributes): array;
    function delete(String $id): Bool;
}
