<?php

namespace App\Repositories\Interfaces;

use App\Models\Client;
use Illuminate\Support\Collection;

interface ClientRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Client;
    function create(array $attributes): Client;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
