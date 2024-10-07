<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductService
{
    protected $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return Collection
     */
    public function getAllProducts(): Collection
    {
        return $this->productRepository->all();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getProduct($id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw new \Exception("Product not found", JsonResponse::HTTP_NOT_FOUND);
        }

        return $product;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function createProduct(array $data)
    {
        $this->validate($data);

        if (isset($data['photo'])) {
            $data['photo'] = $this->uploadImage($data['photo']);
        }

        return $this->productRepository->create($data);
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function updateProduct($id, array $data)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw new \Exception("Product not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $this->validate($data);

        if (isset($data['photo'])) {
            $data['photo'] = $this->uploadImage($data['photo']);
        }

        $this->productRepository->update($id, $data);

        return $product;
    }

    /**
     * @param $id
     * @return void
     * @throws \Exception
     */
    public function deleteProduct($id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw new \Exception("Product not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $this->productRepository->delete($id);
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param $image
     * @return string
     */
    public function uploadImage($image): string
    {
        return $image->store('products', 'public');
    }
}

