<?php

namespace App\Repositories;

use App\Models\DiscountType;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;

use App\Repositories\Interfaces\DiscountTypeRepositoryInterface;

class DiscountTypeRepository implements DiscountTypeRepositoryInterface
{
    public function all(): Collection
    {
        return DiscountType::all();
    }

    public function get($parameters = []): ?Collection
    {
        return DiscountType::where($parameters)->get();
    }

    public function find(String $id): ?DiscountType
    {
        return DiscountType::find($id);
    }

    public function create(array $data): DiscountType
    {
        $discountType = DiscountType::create($data);
        return $discountType;
    }

    public function update(String $id, array $data): Bool
    {
        $discountType = DiscountType::findOrFail($id);
        return $discountType->update($data);
    }

    public function delete(String $id): Bool
    {
        $discountType = DiscountType::findOrFail($id);
        return $discountType->delete();
    }
}
