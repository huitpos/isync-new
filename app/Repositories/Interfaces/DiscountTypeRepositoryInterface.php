<?php

namespace App\Repositories\Interfaces;

use App\Models\DiscountType;
use Illuminate\Support\Collection;

interface DiscountTypeRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?DiscountType;
    function create(array $attributes): DiscountType;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
}
