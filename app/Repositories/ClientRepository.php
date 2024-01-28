<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    public function all(): Collection
    {
        return Client::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Client::where($parameters)->get();
    }

    public function find(String $id): ?Client
    {
        return Client::find($id);
    }

    public function create(array $data): Client
    {
        $client = Client::create($data);
        return $client;
    }

    public function update(String $id, array $data): Bool
    {
        $client = Client::findOrFail($id);
        return $client->update($data);
    }

    public function delete(String $id): Bool
    {
        $client = Client::findOrFail($id);
        return $client->delete();
    }
}
