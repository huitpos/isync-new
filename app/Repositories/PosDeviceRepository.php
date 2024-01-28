<?php

namespace App\Repositories;

use App\Models\PosDevice;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\PosDeviceRepositoryInterface;

class PosDeviceRepository implements PosDeviceRepositoryInterface
{
    public function all(): Collection
    {
        return PosDevice::all();
    }

    public function get($parameters = []): ?Collection
    {
        return PosDevice::where($parameters)->get();
    }

    public function find(String $id): ?PosDevice
    {
        return PosDevice::find($id);
    }

    public function create(array $data): PosDevice
    {
        $device = PosDevice::create($data);
        return $device;
    }

    public function update(String $id, array $data): Bool
    {
        $device = PosDevice::findOrFail($id);
        return $device->update($data);
    }

    public function delete(String $id): Bool
    {
        $device = PosDevice::findOrFail($id);
        return $device->delete();
    }

    public function findOrFail(String $id): ?PosDevice
    {
        return PosDevice::findOrFail($id);
    }
}
