<?php

namespace App\Repositories;

use App\Models\ChargeAccount;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\ChargeAccountRepositoryInterface;

class ChargeAccountRepository implements ChargeAccountRepositoryInterface
{
    public function all(): Collection
    {
        return ChargeAccount::all();
    }

    public function get($parameters = []): ?Collection
    {
        return ChargeAccount::where($parameters)->get();
    }

    public function find(String $id): ?ChargeAccount
    {
        return ChargeAccount::find($id);
    }

    public function create(array $data): ChargeAccount
    {
        $chargeAccount = ChargeAccount::create($data);
        return $chargeAccount;
    }

    public function update(String $id, array $data): Bool
    {
        $chargeAccount = ChargeAccount::findOrFail($id);
        return $chargeAccount->update($data);
    }

    public function delete(String $id): Bool
    {
        $chargeAccount = ChargeAccount::findOrFail($id);
        return $chargeAccount->delete();
    }
}
