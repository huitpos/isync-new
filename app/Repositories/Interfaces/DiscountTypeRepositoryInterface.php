<?php

namespace App\Repositories\Interfaces;

use App\Models\DiscountType;
use Illuminate\Support\Collection;

interface DiscountTypeRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?DiscountType;
    function create(array $attributes, array $fieldsData): DiscountType;
    function update(String $id, array $attributes, array $fieldsData): DiscountType;
    function delete(String $id): Bool;
    function syncDepartments(String $id, array $attributes): array;
}
