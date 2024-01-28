<?php

namespace App\Repositories;

use App\Models\PaymentType;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\PaymentTypeRepositoryInterface;

class PaymentTypeRepository implements PaymentTypeRepositoryInterface
{
    public function all(): Collection
    {
        return PaymentType::all();
    }

    public function get($parameters = []): ?Collection
    {
        return PaymentType::where($parameters)->get();
    }

    public function find(String $id): ?PaymentType
    {
        return PaymentType::with('fields')->find($id);
    }

    public function create(array $paymentTypeData, array $fieldsData): PaymentType
    {
        $paymentType = PaymentType::create($paymentTypeData);

        if (!empty($fieldsData)) {
            $paymentType->fields()->createMany($fieldsData);
        }

        return $paymentType;
    }

    public function update(String $id, array $paymentTypeData, array $fieldsData): PaymentType
    {
        $paymentType = PaymentType::findOrFail($id);
        $paymentType->update($paymentTypeData);

        if (!empty($fieldsData)) {
            $paymentType->fields()->delete();
            $paymentType->fields()->createMany($fieldsData);
        }

        return $paymentType;
    }

    public function delete(String $id): Bool
    {
        $paymentType = PaymentType::findOrFail($id);
        return $paymentType->delete();
    }
}
