<?php

namespace Tests\Unit;

use App\Mail\OrderCreatedMail;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;
    protected $orderRepository;
    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = Mockery::mock(OrderRepository::class);
        $this->userRepository = Mockery::mock(UserRepository::class);

        $this->orderService = new OrderService($this->orderRepository, $this->userRepository);
    }

    public function testGetAllOrdersReturnsCollection()
    {
        $orders = new Collection([new Order(), new Order()]);

        $this->orderRepository->shouldReceive('all')->once()->andReturn($orders);

        $result = $this->orderService->getAllOrders();

        $this->assertEquals($orders, $result);
    }

    public function testGetOrderReturnsOrder()
    {
        $order = Order::factory()->create();

        $this->orderRepository->shouldReceive('find')->with($order->id)->once()->andReturn($order);

        $result = $this->orderService->getOrder($order->id);

        $this->assertEquals($order, $result);
    }

    public function testGetOrderThrowsExceptionIfNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Client not found");

        $this->orderRepository->shouldReceive('find')->with(999)->once()->andReturn(null);

        $this->orderService->getOrder(999);
    }

    public function testCreateOrderSendsEmail()
    {
        Mail::fake();

        $user = User::factory()->create();

        $client = Client::factory()->create(['user_id' => $user->id]);

        $product = Product::factory()->create();

        $orderData = [
            'client_id' => $client->id,
            'product_id' => $product->id,
        ];

        $order = Order::factory()->make($orderData);

        $this->orderRepository->shouldReceive('create')->once()->andReturn($order);

        $createdOrder = $this->orderService->createOrder($orderData);

        Mail::assertSent(OrderCreatedMail::class);

        $this->assertEquals($order->toArray(), $createdOrder->toArray());
    }

    public function testUpdateOrderReturnsUpdatedOrder()
    {
        $order = Order::factory()->create();

        $updatedData = [
            'client_id' => $order->client_id,
            'product_id' => $order->product_id,
        ];

        $this->orderRepository->shouldReceive('find')->with($order->id)->once()->andReturn($order);
        $this->orderRepository->shouldReceive('update')->with($order->id, $updatedData)->once();

        $result = $this->orderService->updateOrder($order->id, $updatedData);

        $this->assertEquals($order, $result);
    }

    public function testUpdateOrderThrowsExceptionIfNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Client not found");

        $this->orderRepository->shouldReceive('find')->with(999)->once()->andReturn(null);

        $this->orderService->updateOrder(999, []);
    }
}
