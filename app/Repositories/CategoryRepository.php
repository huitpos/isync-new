<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all(): Collection
    {
        return Category::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Category::where($parameters)->get();
    }

    public function find(String $id): ?Category
    {
        return Category::find($id);
    }

    public function findOrFail(String $id): ?Category
    {
        return Category::findOrFail($id);
    }

    public function create(array $data): Category
    {
        $category = Category::create($data);
        return $category;
    }

    public function update(String $id, array $data): Bool
    {
        $category = Category::findOrFail($id);
        return $category->update($data);
    }

    public function delete(String $id): Bool
    {
        $category = Category::findOrFail($id);
        return $category->delete();
    }

    public function syncSuppliers(String $id, array $data): array
    {
        $category = Category::findOrFail($id);
        return $category->suppliers()->sync($data);
    }
}
