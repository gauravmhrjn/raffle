<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Product;
use App\Models\RaffleEntry;
use App\Models\User;
use App\Repositories\RaffleEntryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class CheckIfUserHasAlreadyEnteredRaffleAction
{
    public function __construct(
        private RaffleEntryRepositoryInterface $raffleEntryRepository
    ) {}

    public function handle(User $user, Product $product): RaffleEntry|bool
    {
        try {
            return $this->raffleEntryRepository->findPendingByUserAndProduct($user, $product);
        } catch (ModelNotFoundException $exception) {
            return false;
        }
    }
}
