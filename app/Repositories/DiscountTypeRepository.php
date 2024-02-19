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

    public function create(array $data, array $fieldsData): DiscountType
    {
        $discountType = DiscountType::create($data);

        if (!empty($fieldsData)) {
            $discountType->fields()->createMany($fieldsData);
        }

        return $discountType;
    }

    public function update(String $id, array $data, array $fieldsData): DiscountType
    {
        $discountType = DiscountType::findOrFail($id);
        $discountType->update($data);

        if (!empty($fieldsData)) {
            $discountType->fields()->delete();
            $discountType->fields()->createMany($fieldsData);
        }

        return $discountType;
    }

    public function delete(String $id): Bool
    {
        $discountType = DiscountType::findOrFail($id);
        return $discountType->delete();
    }
}
