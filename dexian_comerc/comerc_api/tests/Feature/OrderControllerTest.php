<?php

namespace Tests\Unit;

use App\Http\Controllers\OrderController;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->orderService = $this->mock(OrderService::class);
    }

    public function testIndexReturnsAllOrders()
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(5)->make();

        $this->orderService->shouldReceive('getAllOrders')->once()->andReturn($orders);

        $response = $this->actingAs($user)->getJson('/api/orders');

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson($orders->toArray());
    }

    public function testShowReturnsOrder()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $this->orderService->shouldReceive('getOrder')->with($order->id)->once()->andReturn($order);

        $response = $this->actingAs($user)->getJson('/api/orders/' . $order->id);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson($order->toArray());
    }

    public function testShowThrowsException()
    {
        $this->orderService->shouldReceive('getOrder')
            ->andThrow(new \Exception('Order not found', JsonResponse::HTTP_NOT_FOUND));

        $response = $this->json('GET', '/orders/1');

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'The route orders/1 could not be found.',
            ]);
    }

    public function testStoreThrowsException()
    {
        $this->orderService->shouldReceive('createOrder')
            ->andThrow(new \Exception('Failed to create order', JsonResponse::HTTP_NOT_FOUND));

        $response = $this->json('POST', '/orders', [
            'product_id' => 1,
            'quantity' => 2,
        ]);

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'The route orders could not be found.',
            ]);
    }

    public function testUpdateThrowsException()
    {
        $this->orderService->shouldReceive('updateOrder')
            ->andThrow(new \Exception('Failed to update order', JsonResponse::HTTP_NOT_FOUND));

        $response = $this->json('PUT', '/orders/1', [
            'product_id' => 1,
            'quantity' => 3,
        ]);

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'The route orders/1 could not be found.',
            ]);
    }


    public function testStoreCreatesOrder()
    {
        $user = User::factory()->create();
        $data = ['product_id' => 1, 'client_id' => 2];

        $this->orderService->shouldReceive('createOrder')->once()->with($data)->andReturn(new Order($data));

        $response = $this->actingAs($user)->postJson('/api/orders', $data);

        $response->assertStatus(JsonResponse::HTTP_CREATED);
        $response->assertJson([
            'message' => 'Order created successfully.',
            'order' => $data,
        ]);
    }

    public function testUpdateUpdatesOrder()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $data = ['product_id' => 1, 'client_id' => 3];

        $this->orderService->shouldReceive('updateOrder')->with($order->id, $data)->once()->andReturn(new Order($data));

        $response = $this->actingAs($user)->putJson('/api/orders/' . $order->id, $data);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'message' => 'Order updated successfully.',
            'order' => $data,
        ]);
    }
}
