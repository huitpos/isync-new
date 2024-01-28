<?php

namespace App\Repositories\Interfaces;

use App\Models\PosDevice;
use Illuminate\Support\Collection;

interface PosDeviceRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?PosDevice;
    function findOrFail(String $id): ?PosDevice;
    function create(array $attributes): PosDevice;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
