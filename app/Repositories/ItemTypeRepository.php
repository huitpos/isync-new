<?php

namespace App\Repositories;

use App\Models\ItemType;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\ItemTypeRepositoryInterface;

class ItemTypeRepository implements ItemTypeRepositoryInterface
{
    public function all(): Collection
    {
        return ItemType::all();
    }

    public function get($parameters = []): ?Collection
    {
        return ItemType::where($parameters)->get();
    }

    public function find(String $id): ?ItemType
    {
        return ItemType::find($id);
    }

    public function create(array $data): ItemType
    {
        $uom = ItemType::create($data);
        return $uom;
    }

    public function update(String $id, array $data): Bool
    {
        $uom = ItemType::findOrFail($id);
        return $uom->update($data);
    }

    public function delete(String $id): Bool
    {
        $uom = ItemType::findOrFail($id);
        return $uom->delete();
    }
}
