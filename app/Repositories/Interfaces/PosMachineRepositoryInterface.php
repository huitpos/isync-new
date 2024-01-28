<?php

namespace App\Repositories\Interfaces;

use App\Models\PosMachine;
use Illuminate\Support\Collection;

interface PosMachineRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?PosMachine;
    function create(array $attributes): PosMachine;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
    function getAllUnderCompany(String $companyId): ?Collection;
}
