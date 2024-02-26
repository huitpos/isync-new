<?php

namespace App\Repositories\Interfaces;

use App\Models\UnitOfMeasurement;
use Illuminate\Support\Collection;

interface UnitOfMeasurementRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?UnitOfMeasurement;
    function create(array $attributes): UnitOfMeasurement;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
    function syncConversion(String $id, array $attributes): Collection;
}
