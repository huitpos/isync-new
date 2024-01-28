<?php

namespace App\Repositories;

use App\Models\Subcategory;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\SubcategoryRepositoryInterface;

class SubcategoryRepository implements SubcategoryRepositoryInterface
{
    public function all(): Collection
    {
        return Subcategory::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Subcategory::where($parameters)->get();
    }

    public function find(String $id): ?Subcategory
    {
        return Subcategory::find($id);
    }

    public function findOrFail(String $id): ?Subcategory
    {
        return Subcategory::findOrFail($id);
    }

    public function create(array $data): Subcategory
    {
        $subcategory = Subcategory::create($data);
        return $subcategory;
    }

    public function update(String $id, array $data): Bool
    {
        $subcategory = Subcategory::findOrFail($id);
        return $subcategory->update($data);
    }

    public function delete(String $id): Bool
    {
        $subcategory = Subcategory::findOrFail($id);
        return $subcategory->delete();
    }

    public function syncSuppliers(String $id, array $data): array
    {
        $subcategory = Subcategory::findOrFail($id);
        return $subcategory->suppliers()->sync($data);
    }
}
