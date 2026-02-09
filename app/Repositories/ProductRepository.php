<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\ProductNotFoundException;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

final readonly class ProductRepository implements ProductRepositoryInterface
{
    private const string TABLE_NAME = 'products';

    private const int PAGE_LIMIT = 6;

    public function getActiveProductsWithBrandAndCategoryAndPageination(): Paginator
    {
        return Product::with(['brand', 'category'])->active()->simplePaginate(self::PAGE_LIMIT);
    }

    public function findActiveProductBySlugWithBrandAndCategory(string $slug): Product
    {
        try {
            return Product::with(['brand', 'category'])->active()->forSlug($slug)->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new ProductNotFoundException(
                ProductNotFoundException::ERROR_MESSAGE
            );
        }
    }

    public function findActiveProductById(int $id): Product
    {
        try {
            return Product::active()->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ProductNotFoundException(
                ProductNotFoundException::ERROR_MESSAGE
            );
        }
    }

    public function findActiveProductToRaffleById(int $id): Product
    {
        return Product::active()->toRaffle()->findOrFail($id);
    }

    /**
     * @return array<int>
     */
    public function getActiveProductIdsToRaffle(): array
    {
        return Product::active()->toRaffle()->pluck('id')->toArray();
    }

    public function decrementStockQty(int $productId): void
    {
        DB::transaction(function () use ($productId) {
            DB::table(self::TABLE_NAME)
                ->lockForUpdate()
                ->where('id', $productId)
                ->decrement('qty');
        });
    }
}
