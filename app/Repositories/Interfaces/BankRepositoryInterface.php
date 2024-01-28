<?php

namespace App\Repositories\Interfaces;

use App\Models\Bank;
use Illuminate\Support\Collection;

interface BankRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Bank;
    function create(array $attributes): Bank;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
