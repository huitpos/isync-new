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
        $user = User::create($data);
        return $user;
    }

    public function update(String $id, array $data): Bool
    {
        $user = User::findOrFail($id);
        return $user->update($data);
    }

    public function delete(String $id): Bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
