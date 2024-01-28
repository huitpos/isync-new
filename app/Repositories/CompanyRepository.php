<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\CompanyRepositoryInterface;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function all(): Collection
    {
        return Company::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Company::where($parameters)->get();
    }

    public function find(String $id): ?Company
    {
        return Company::find($id);
    }

    public function create(array $data): Company
    {
        $company = Company::create($data);
        return $company;
    }

    public function update(String $id, array $data): Bool
    {
        $company = Company::findOrFail($id);
        return $company->update($data);
    }

    public function delete(String $id): Bool
    {
        $company = Company::findOrFail($id);
        return $company->delete();
    }
}
