<?php

namespace App\Repositories;

use App\Models\Bank;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\BankRepositoryInterface;

class BankRepository implements BankRepositoryInterface
{
    public function all(): Collection
    {
        return Bank::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Bank::where($parameters)->get();
    }

    public function find(String $id): ?Bank
    {
        return Bank::find($id);
    }

    public function create(array $data): Bank
    {
        $bank = Bank::create($data);
        return $bank;
    }

    public function update(String $id, array $data): Bool
    {
        $bank = Bank::findOrFail($id);
        return $bank->update($data);
    }

    public function delete(String $id): Bool
    {
        $bank = Bank::findOrFail($id);
        return $bank->delete();
    }
}
