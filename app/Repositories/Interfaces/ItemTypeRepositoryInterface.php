<?php

namespace App\Repositories\Interfaces;

use App\Models\ItemType;
use Illuminate\Support\Collection;

interface ItemTypeRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?ItemType;
    function create(array $attributes): ItemType;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
