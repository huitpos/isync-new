<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Product;
    function create(array $productData, array $bundleData, array $rawData): Product;
    function update(String $id, array $productData, array $bundleData, array $rawData): Product;
    function delete(String $id): Bool;
}
