<?php

namespace App\Repositories\Interfaces;

use App\Models\Supplier;
use Illuminate\Support\Collection;

interface SupplierRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Supplier;
    function findOrFail(String $id): ?Supplier;
    function create(array $attributes): Supplier;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
