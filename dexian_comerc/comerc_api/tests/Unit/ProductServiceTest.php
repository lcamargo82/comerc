<?php

namespace Tests\Unit;

use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class ProductServiceTest extends TestCase
{
    protected $productRepository;
    protected $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = Mockery::mock(ProductRepository::class);
        $this->productService = new ProductService($this->productRepository);
    }

    public function testGetAllProducts()
    {
        $products = new Collection([
            ['id' => 1, 'name' => 'Product 1'],
            ['id' => 2, 'name' => 'Product 2'],
        ]);

        $this->productRepository->shouldReceive('all')->once()->andReturn($products);

        $result = $this->productService->getAllProducts();

        $this->assertEquals($products, $result);
    }

    public function testGetProduct()
    {
        $product = ['id' => 1, 'name' => 'Product 1'];
        $this->productRepository->shouldReceive('find')->with(1)->once()->andReturn($product);

        $result = $this->productService->getProduct(1);

        $this->assertEquals($product, $result);
    }

    public function testGetProductNotFound()
    {
        $this->productRepository->shouldReceive('find')->with(999)->once()->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not found');

        $this->productService->getProduct(999);
    }

    public function testCreateProduct()
    {
        $data = [
            'name' => 'Product 1',
            'price' => 100.0,
            'photo' => UploadedFile::fake()->image('product.jpg'),
        ];

        $this->productRepository->shouldReceive('create')->once()->andReturn($data);

        // Agora executa o serviço sem tentar mockar o upload
        $result = $this->productService->createProduct($data);

        $this->assertEquals($data, $result);
    }

    public function testCreateProductWithValidationError()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'name' => '',
            'price' => -10,
            'photo' => null,
        ];

        $this->productService->createProduct($data);
    }

    public function testUpdateProductNotFound()
    {
        $this->productRepository->shouldReceive('find')->with(999)->once()->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not found');

        $this->productService->updateProduct(999, []);
    }

    public function testDeleteProduct()
    {
        $product = ['id' => 1, 'name' => 'Product 1'];

        $this->productRepository->shouldReceive('find')->with(1)->once()->andReturn($product);
        $this->productRepository->shouldReceive('delete')->with(1)->once();

        $this->productService->deleteProduct(1);
    }

    public function testDeleteProductNotFound()
    {
        $this->productRepository->shouldReceive('find')->with(999)->once()->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not found');

        $this->productService->deleteProduct(999);
    }
}
