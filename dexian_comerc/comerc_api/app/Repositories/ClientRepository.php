<?php

namespace App\Repositories;

use App\Models\Client;

class ClientRepository
{
    public function all()
    {
        return Client::all();
    }

    public function find($id)
    {
        return Client::find($id);
    }

    public function create(array $data)
    {
        return Client::create($data);
    }

    public function update($id, array $data)
    {
        $client = $this->find($id);
        $client->update($data);
        return $client;
    }

    public function delete($id)
    {
        return Client::destroy($id);
    }
}
