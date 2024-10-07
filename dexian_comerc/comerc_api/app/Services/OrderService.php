<?php

namespace App\Services;

use App\Mail\OrderCreatedMail;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
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
            throw new \Exception("Order not found", JsonResponse::HTTP_NOT_FOUND);
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

        $validEmail = filter_var($order->client->user->email, FILTER_VALIDATE_EMAIL);

        if (isset($order->client->user->email) && $validEmail) {
            Mail::to($order->client->user->email)->send(new OrderCreatedMail($order));
        } else {
            throw new \Exception("The user does not have a valid email address.");
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
            throw new \Exception("Order not found", JsonResponse::HTTP_NOT_FOUND);
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
