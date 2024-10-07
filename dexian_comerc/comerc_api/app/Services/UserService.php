<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @return Collection
     */
    public function getAllUsers()
    {
        return $this->userRepository->all();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getUser($id)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new \Exception("Client not found",JsonResponse::HTTP_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function createUser(array $data)
    {
        $this->validate($data);

        return $this->userRepository->create($data);
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function updateUser($id, array $data)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new \Exception("Client not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $this->validateForUpdate($data);

        $user->name = $data['name'];

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (isset($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $this->userRepository->update($id, $user->toArray());

        return $user;
    }

    /**
     * @param $id
     * @return int
     * @throws \Exception
     */
    public function deleteUser($id)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new \Exception("Client not found", JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->userRepository->delete($id);
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validateForUpdate(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
