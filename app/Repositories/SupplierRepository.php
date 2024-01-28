<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\SupplierRepositoryInterface;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function all(): Collection
    {
        return Supplier::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Supplier::where($parameters)->get();
    }

    public function find(String $id): ?Supplier
    {
        return Supplier::find($id);
    }

    public function create(array $data): Supplier
    {
        $supplier = Supplier::create($data);
        return $supplier;
    }

    public function update(String $id, array $data): Bool
    {
        $supplier = Supplier::findOrFail($id);
        return $supplier->update($data);
    }

    public function delete(String $id): Bool
    {
        $supplier = Supplier::findOrFail($id);
        return $supplier->delete();
    }

    public function findOrFail(String $id): ?Supplier
    {
        return Supplier::findOrFail($id);
    }
}
