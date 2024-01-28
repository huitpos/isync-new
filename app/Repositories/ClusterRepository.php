<?php

namespace App\Repositories;

use App\Models\Cluster;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\ClusterRepositoryInterface;

class ClusterRepository implements ClusterRepositoryInterface
{
    public function all(): Collection
    {
        return Cluster::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Cluster::where($parameters)->get();
    }

    public function find(String $id): ?Cluster
    {
        return Cluster::find($id);
    }

    public function create(array $data): Cluster
    {
        $cluster = Cluster::create($data);
        return $cluster;
    }

    public function update(String $id, array $data): Bool
    {
        $cluster = Cluster::findOrFail($id);
        return $cluster->update($data);
    }

    public function delete(String $id): Bool
    {
        $cluster = Cluster::findOrFail($id);
        return $cluster->delete();
    }
}
