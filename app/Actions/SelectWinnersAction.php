<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\ChargeWinnerJob;
use App\Models\Product;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\RaffleEntryRepositoryInterface;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

final readonly class SelectWinnersAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private RaffleEntryRepositoryInterface $raffleEntryRepository
    ) {}

    public function handle(int $productId): void
    {
        $product = $this->productRepository->findActiveProductToRaffleById($productId);

        if (! $this->checkIfProductHasStockToProceed($product)) {
            Log::warning('SelectWinnersAction failed', [
                'productId' => $product->id,
                'message' => 'Product does not have stock.',
            ]);

            return;
        }

        if (! $this->checkIfRaffleHasEntriesToProceed($product)) {
            Log::warning('SelectWinnersAction failed', [
                'productId' => $product->id,
                'message' => 'No entries in raffle.',
            ]);

            return;
        }

        $this->selectAndChargeWinners($product);

        Log::info('SelectWinnersAction executed', [
            'productId' => $productId,
        ]);
    }

    private function checkIfProductHasStockToProceed(Product $product): bool
    {
        return $product->qty > 0;
    }

    private function checkIfRaffleHasEntriesToProceed(Product $product): bool
    {
        return $this->raffleEntryRepository->getEntryCount($product) > 0;
    }

    private function selectAndChargeWinners(Product $product): void
    {
        $batchName = sprintf('charge_raffle_winners_%s', $product->id);

        $winnerEntryIds = $this->raffleEntryRepository->randomlySelectWinners($product);

        $chargeWinnerJobsArray = array_map(function ($raffleEntryId) {
            return new ChargeWinnerJob($raffleEntryId);
        }, $winnerEntryIds);

        Bus::batch($chargeWinnerJobsArray)
            ->name($batchName)
            ->allowFailures()
            ->dispatch();
    }
}
