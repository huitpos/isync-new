<?php

namespace App\Repositories\Interfaces;

use App\Models\Subcategory;
use Illuminate\Support\Collection;

interface SubcategoryRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Subcategory;
    function findOrFail(String $id): ?Subcategory;
    function create(array $attributes): Subcategory;
    function update(String $id, array $attributes): Bool;
    function syncSuppliers(String $id, array $attributes): array;
    function delete(String $id): Bool;
}
