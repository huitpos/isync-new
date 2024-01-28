<?php

namespace App\Repositories;

use App\Models\PosMachine;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;

use App\Repositories\Interfaces\PosMachineRepositoryInterface;

use Illuminate\Support\Str;

class PosMachineRepository implements PosMachineRepositoryInterface
{
    public function all(): Collection
    {
        return PosMachine::all();
    }

    public function get($parameters = []): ?Collection
    {
        return PosMachine::with([
            'branch' => [
                'cluster',
                'region',
                'province',
                'city',
                'barangay',
                'company' => [
                    'region',
                    'province',
                    'city',
                    'barangay',
                ]
            ]
        ])->where($parameters)->get();
    }

    public function find(String $id): ?PosMachine
    {
        return PosMachine::find($id);
    }

    public function create(array $data): PosMachine
    {
        $data['product_key'] = strtoupper(str::random(5) . "-" . str::random(5) . "-" . str::random(5) . "-" . str::random(5) . "-" . str::random(5));

        $posMachine = PosMachine::create($data);
        return $posMachine;
    }

    public function update(String $id, array $data): Bool
    {
        $posMachine = PosMachine::findOrFail($id);
        return $posMachine->update($data);
    }

    public function delete(String $id): Bool
    {
        $posMachine = PosMachine::findOrFail($id);
        return $posMachine->delete();
    }

    public function getAllUnderCompany(String $companyId): ?Collection
    {
        $machines = DB::table('pos_machines')
            ->join('branches', 'branches.id', '=', 'pos_machines.branch_id')
            ->where('branches.company_id', '=', $companyId)
            ->select('pos_machines.*')
            ->get();

        return $machines;
    }
}
