<?php

namespace App\services;

use App\Repositories\ClientRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClientService
{
    protected $clientRepository;

    /**
     * @param ClientRepository $clientRepository
     */
    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * @return Collection
     */
    public function getAllClients()
    {
        return $this->clientRepository->all();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getClient($id)
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            throw new \Exception("Client not found", 404);
        }

        return $client;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function createClient(array $data)
    {
        $this->validate($data);

        return $this->clientRepository->create($data);
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function updateClient($id, array $data)
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            throw new \Exception("Client not found", 404);
        }

        $this->validate($data);

        $this->clientRepository->update($client, $data);

        return $client;
    }

    /**
     * @param $id
     * @return int
     * @throws \Exception
     */
    public function deleteClient($id)
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            throw new \Exception("Client not found", 404);
        }

        return $this->clientRepository->delete($client);
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validate(array $data)
    {
        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'phone' => 'required|string|max:15',
            'birth_date' => 'nullable|date',
            'address' => 'required|string',
            'complement' => 'nullable|string',
            'district' => 'required|string',
            'zipcode' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
