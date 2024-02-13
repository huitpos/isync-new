<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?User;
    function create(array $attributes): User;
    function update(String $id, array $attributes, $syncRoles, $syncBranches): Bool;
    function delete(String $id): Bool;
}
