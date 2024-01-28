<?php

namespace App\Repositories\Interfaces;

use App\Models\PaymentType;
use Illuminate\Support\Collection;

interface PaymentTypeRepositoryInterface
{
    function all(): Collection;
    function get(array $parameter): ?Collection;
    function find(String $id): ?PaymentType;
    function create(array $paymentTypeData, array $fieldsData): PaymentType;
    function update(String $id, array $paymentTypeData, array $fieldsData): PaymentType;
    function delete(String $id): Bool;
}
