<?php

namespace App\Repositories\Interfaces;

use App\Models\Branch;
use Illuminate\Support\Collection;

interface BranchRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Branch;
    function create(array $attributes): Branch;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
    function getTransactionAmount(String $id): float;
    function getTransactionCostAmount(String $id): float;
    function getTransactionCount(String $id): int;
    function getCompletedTransactions(String $id): ?Collection;
    function getPendingTransactions(String $id): ?Collection;
}
