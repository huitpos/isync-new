<?php

namespace App\Repositories;

use App\Models\UnitOfMeasurement;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\UnitOfMeasurementRepositoryInterface;

class UnitOfMeasurementRepository implements UnitOfMeasurementRepositoryInterface
{
    public function all(): Collection
    {
        return UnitOfMeasurement::all();
    }

    public function get($parameters = []): ?Collection
    {
        return UnitOfMeasurement::where($parameters)->get();
    }

    public function find(String $id): ?UnitOfMeasurement
    {
        return UnitOfMeasurement::find($id);
    }

    public function create(array $data): UnitOfMeasurement
    {
        $uom = UnitOfMeasurement::create($data);
        return $uom;
    }

    public function update(String $id, array $data): Bool
    {
        $uom = UnitOfMeasurement::findOrFail($id);
        return $uom->update($data);
    }

    public function delete(String $id): Bool
    {
        $uom = UnitOfMeasurement::findOrFail($id);
        return $uom->delete();
    }
}
