<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function all(): Collection
    {
        return User::all();
    }

    public function get($parameters = []): ?Collection
    {
        return User::where($parameters)->get();
    }

    public function find(String $id): ?User
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        $role = $data['role'];
        $branches = $data['branches'] ?? null;

        unset($data['role']);
        unset($data['branches']);

        $user = User::create($data);

        $user->assignRole($role);

        if ($branches) {
            $user->branches()->attach($branches);
        }

        return $user;
    }

    public function update(String $id, array $data, $syncRoles = true, $syncBranches = true): Bool
    {
        $user = User::findOrFail($id);

        $role = $data['role'] ?? null;
        $branches = $data['branches'] ?? null;

        unset($data['role']);
        unset($data['branches']);

        if ($syncRoles) {
            $user->syncRoles([]);
            $user->assignRole($role);
        }

        if ($syncBranches) {
            $user->branches()->detach();
            $user->branches()->attach($branches);
        }

        return $user->update($data);
    }

    public function delete(String $id): Bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
