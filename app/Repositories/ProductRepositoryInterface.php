<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\Paginator;

interface ProductRepositoryInterface
{
    public function getActiveProductsWithBrandAndCategoryAndPageination(): Paginator;

    public function findActiveProductBySlugWithBrandAndCategory(string $slug): Product;

    public function findActiveProductById(int $id): Product;

    public function findActiveProductToRaffleById(int $id): Product;

    /**
     * @return array<int>
     */
    public function getActiveProductIdsToRaffle(): array;

    public function decrementStockQty(int $productId): void;
}
