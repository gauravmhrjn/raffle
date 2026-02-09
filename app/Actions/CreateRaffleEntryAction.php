<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\RaffleEntryDTO;
use App\Events\RaffleEntryCreated;
use App\Models\RaffleEntry;
use App\Repositories\RaffleEntryRepositoryInterface;

final readonly class CreateRaffleEntryAction
{
    public function __construct(
        private CheckIfUserHasAlreadyEnteredRaffleAction $checkIfUserHasAlreadyEnteredRaffleAction,
        private RaffleEntryRepositoryInterface $raffleEntryRepository
    ) {}

    public function handle(
        RaffleEntryDTO $raffleEntryDTO
    ): RaffleEntry {
        $raffleEntryExist = $this->checkIfUserHasAlreadyEnteredRaffleAction->handle($raffleEntryDTO->user, $raffleEntryDTO->product);

        if ($raffleEntryExist) {
            return $raffleEntryExist;
        }

        $raffleEntry = $this->raffleEntryRepository->save($raffleEntryDTO);

        RaffleEntryCreated::dispatch($raffleEntry);

        return $raffleEntry;
    }
}
