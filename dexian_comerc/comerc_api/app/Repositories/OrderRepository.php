<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function all()
    {
        return Order::all();
    }

    public function find($id)
    {
        return Order::find($id);
    }

    public function create(array $data)
    {
        return Order::create($data);
    }

    public function update($id, array $data)
    {
        $order = $this->find($id);
        $order->update($data);
        return $order;
    }
}
