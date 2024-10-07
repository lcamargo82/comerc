<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $productService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->productService = $this->mock(ProductService::class);
    }

    public function testCannotListProductsWithoutAuthentication()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function testCannotAccessProductListWhenNotAuthenticated()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function testIndexReturnsAllProducts()
    {
        $user = User::factory()->create();

        $products = Product::factory()->count(5)->make();

        $this->productService->shouldReceive('getAllProducts')->once()->andReturn($products);

        $response = $this->actingAs($user)->getJson('/api/products');

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson($products->toArray());
    }

    public function testShowReturnsProduct()
    {
        $user = User::factory()->create();

        $product = Product::factory()->make();

        $this->productService->shouldReceive('getProduct')->with(1)->once()->andReturn($product);

        $response = $this->actingAs($user)->getJson('/api/products/1');

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson($product->toArray());
    }

    public function testShowReturns404WhenProductNotFound()
    {
        $user = User::factory()->create();

        $this->productService->shouldReceive('getProduct')->with(999)->once()->andThrow(new \Exception('Product not found', 404));

        $response = $this->actingAs($user)->getJson('/api/products/999');

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJson([
            'message' => 'Product not found',
        ]);
    }

    public function testStoreCreatesProduct()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Product Test',
            'price' => 100,
            'description' => 'Product description',
        ];

        $product = Product::factory()->make($data);

        $this->productService->shouldReceive('createProduct')->with($data)->once()->andReturn($product);

        $response = $this->actingAs($user)->postJson('/api/products', $data);

        $response->assertStatus(JsonResponse::HTTP_CREATED);
        $response->assertJson([
            'message' => 'Product created successfully.',
            'product' => $product->toArray(),
        ]);
    }

    public function testStoreReturnsValidationError()
    {
        $user = User::factory()->create();

        $data = [
            'name' => '',
            'price' => '',
        ];

        $response = $this->actingAs($user)->postJson('/api/products', $data);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST);
    }

    public function testStoreReturnsValidationErrorWhenImageIsMissing()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Product Test',
            'price' => 100,
            'description' => 'Product description',
            'photo' => null
        ];

        $this->productService->shouldReceive('createProduct')->with($data)->once()->andThrow(new \Exception('The photo field is required.', 400));

        $response = $this->actingAs($user)->postJson('/api/products', $data);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'The photo field is required.',
            ]);
    }

    public function testUpdateUpdatesProduct()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Product',
            'price' => 150,
        ];

        $product = Product::factory()->make();

        $this->productService->shouldReceive('updateProduct')->with(1, $data)->once()->andReturn($product);

        $response = $this->actingAs($user)->putJson('/api/products/1', $data);

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'message' => 'Product updated successfully.',
            'product' => $product->toArray(),
        ]);
    }

    public function testUpdateReturns404WhenProductNotFound()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Product',
            'price' => 150,
        ];

        $this->productService->shouldReceive('updateProduct')->with(999, $data)->once()->andThrow(new \Exception('Product not found', 404));

        $response = $this->actingAs($user)->putJson('/api/products/999', $data);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Product not found',
        ]);
    }

    public function testDestroyDeletesProduct()
    {
        $user = User::factory()->create();

        $this->productService->shouldReceive('deleteProduct')->with(1)->once()->andReturn(true);

        $response = $this->actingAs($user)->deleteJson('/api/products/1');

        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'message' => 'Product deleted successfully',
        ]);
    }

    public function testDestroyReturns404WhenProductNotFound()
    {
        $user = User::factory()->create();

        $this->productService->shouldReceive('deleteProduct')->with(999)->once()->andThrow(new \Exception('Product not found', 404));

        $response = $this->actingAs($user)->deleteJson('/api/products/999');

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Product not found',
        ]);
    }
}
