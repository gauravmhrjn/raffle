<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\ProductNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

final class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function index(): ResourceCollection
    {
        $products = $this->productRepository->getActiveProductsWithBrandAndCategoryAndPageination();

        return new ProductCollection($products);
    }

    public function show(string $slug): ProductResource|JsonResponse
    {
        try {
            $product = $this->productRepository->findActiveProductBySlugWithBrandAndCategory($slug);

            return new ProductResource($product);

        } catch (ProductNotFoundException $exception) {
            return response()->json([
                'status' => 'failed',
                'error' => ProductNotFoundException::ERROR_MESSAGE,
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
