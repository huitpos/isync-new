<?php

namespace App\Repositories\Interfaces;

use App\Models\Branch;
use Illuminate\Support\Collection;

interface BranchRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Branch;
    function create(array $attributes): Branch;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
