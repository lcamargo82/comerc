<?php

namespace App\Services;

use App\Mail\OrderCreatedMail;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderService
{
    protected $orderRepository;

    protected $userRepository;

    /**
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository, UserRepository $userRepository)
    {
        $this->orderRepository = $orderRepository;

        $this->userRepository = $userRepository;
    }

    /**
     * @return Collection
     */
    public function getAllOrders()
    {
        return $this->orderRepository->all();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getOrder($id)
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            throw new \Exception("Client not found", 404);
        }

        return $order;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function createOrder(array $data)
    {
        $this->validate($data);

        $order = $this->orderRepository->create($data);
        $order->load('client', 'product');

        if (isset($order->client->user->email)) {
            Mail::to($order->client->user->email)->send(new OrderCreatedMail($order));
        } else {
            throw new \Exception("O usuário não tem um e-mail associado.");
        }

        return $order;
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function updateOrder($id, array $data)
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            throw new \Exception("Client not found", 404);
        }

        $this->validate($data);

        $this->orderRepository->update($id, $data);

        return $order;
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validate(array $data)
    {
        $validator = Validator::make($data, [
            'client_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
