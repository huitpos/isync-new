<?php

namespace App\Repositories\Interfaces;

use App\Models\ChargeAccount;
use Illuminate\Support\Collection;

interface ChargeAccountRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?ChargeAccount;
    function create(array $attributes): ChargeAccount;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
