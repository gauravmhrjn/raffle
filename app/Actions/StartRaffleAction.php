<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\SelectWinnersJob;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Log;

final readonly class StartRaffleAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function handle(): void
    {
        $productIds = $this->productRepository->getActiveProductIdsToRaffle();

        if (count($productIds) === 0) {
            Log::info('StartRaffleAction stopped', [
                'message' => 'No products to raffle at the moment',
            ]);

            return;
        }

        foreach ($productIds as $productId) {
            SelectWinnersJob::dispatch($productId);
        }

        Log::info('StartRaffleAction executed', [
            'productIds' => $productIds,
        ]);
    }
}
