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

    public function syncConversion(String $id, array $data): Collection
    {
        $unitOfMeasurement = UnitOfMeasurement::find($id);

        $previousConversions = $unitOfMeasurement->conversions()->pluck('id')->toArray();

        $unitOfMeasurement->conversions()
            ->whereIn('id', $previousConversions)
            ->where('company_id', $unitOfMeasurement->company_id)
            ->where('from_unit_id', $unitOfMeasurement->id)
            ->delete();

        if (empty($data)) {
            return collect([]);
        }

        return $unitOfMeasurement->conversions()->createMany($data);
    }
}
