<?php

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\DepartmentRepositoryInterface;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function all(): Collection
    {
        return Department::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Department::where($parameters)->get();
    }

    public function find(String $id): ?Department
    {
        return Department::find($id);
    }

    public function create(array $data): Department
    {
        $department = Department::create($data);
        return $department;
    }

    public function update(String $id, array $data): Bool
    {
        $department = Department::findOrFail($id);
        return $department->update($data);
    }

    public function delete(String $id): Bool
    {
        $department = Department::findOrFail($id);
        return $department->delete();
    }

    public function findOrFail(String $id): ?Department
    {
        return Department::findOrFail($id);
    }

    public function syncSuppliers(String $id, array $data): array
    {
        $department = Department::findOrFail($id);
        return $department->suppliers()->sync($data);
    }
}
