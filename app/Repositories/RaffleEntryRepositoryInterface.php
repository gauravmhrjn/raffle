<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\RaffleEntryDTO;
use App\Models\Product;
use App\Models\RaffleEntry;
use App\Models\User;

interface RaffleEntryRepositoryInterface
{
    public function findById(int $raffleEntryId): RaffleEntry;

    public function findPendingByUserAndProduct(User $user, Product $product): RaffleEntry;

    public function save(RaffleEntryDTO $raffleEntryDTO): RaffleEntry;

    public function getEntryCount(Product $product): int;

    public function setWinner(RaffleEntry $raffleEntry): void;

    /**
     * @return array<int>
     */
    public function randomlySelectWinners(Product $product): array;
}
