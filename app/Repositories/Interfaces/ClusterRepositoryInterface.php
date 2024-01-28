<?php

namespace App\Repositories\Interfaces;

use App\Models\Cluster;
use Illuminate\Support\Collection;

interface ClusterRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Cluster;
    function create(array $attributes): Cluster;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
