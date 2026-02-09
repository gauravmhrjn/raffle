<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Product;
use App\Models\User;

final readonly class CancelRaffleEntryAction
{
    public function __construct(
        private CheckIfUserHasAlreadyEnteredRaffleAction $checkIfUserHasAlreadyEnteredRaffleAction
    ) {}

    public function handle(User $user, Product $product): void
    {
        $raffleEntry = $this->checkIfUserHasAlreadyEnteredRaffleAction->handle($user, $product);

        if (! $raffleEntry) {
            return;
        }

        $raffleEntry->delete();
    }
}
