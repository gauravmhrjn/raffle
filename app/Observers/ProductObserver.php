<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;

final readonly class ProductObserver
{
    public function created(Product $product): void
    {
        cache()->flush();
    }

    public function updated(Product $product): void
    {
        cache()->flush();
    }

    public function deleted(Product $product): void
    {
        cache()->flush();
    }
}
