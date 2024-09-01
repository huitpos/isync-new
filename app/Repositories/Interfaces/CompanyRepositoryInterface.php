<?php

namespace App\Repositories\Interfaces;

use App\Models\Company;
use Illuminate\Support\Collection;

interface CompanyRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?Company;
    function create(array $attributes): Company;
    function update(String $id, array $attributes): Bool;
    function delete(String $id): Bool;
    function getTransactionAmount(String $id): float;
    function getTransactionCostAmount(String $id): float;
    function getTransactionCount(String $id, string $startDate, string $endDate, string $branchId): int;
    function getCompletedTransactions(String $id): ?Collection;
    function getPendingTransactions(String $id): ?Collection;
    function getTransactionNetSales(String $id, string $startDate, string $endDate, string $branchId): ?float;
    function getTransactionGrossSales(String $id, string $startDate, string $endDate, string $branchId): ?float;
}
