<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\RaffleEntryDTO;
use App\Enum\RaffleStatus;
use App\Models\Product;
use App\Models\RaffleEntry;
use App\Models\User;
use Illuminate\Support\Str;

final readonly class RaffleEntryRepository implements RaffleEntryRepositoryInterface
{
    public function findById(int $raffleEntryId): RaffleEntry
    {
        return RaffleEntry::findOrFail($raffleEntryId);
    }

    public function findPendingByUserAndProduct(User $user, Product $product): RaffleEntry
    {
        return RaffleEntry::pending()->forUser($user)->forProduct($product)->firstOrFail();
    }

    public function save(RaffleEntryDTO $raffleEntryDTO): RaffleEntry
    {
        return RaffleEntry::create([
            'user_id' => $raffleEntryDTO->user->id,
            'address_id' => $raffleEntryDTO->address->id,
            'product_id' => $raffleEntryDTO->product->id,
            'status' => $raffleEntryDTO->status->value,
            'encrypted_payment_token' => $raffleEntryDTO->paymentToken,
            'entry_code' => Str::uuid()->toString(),
        ]);
    }

    public function getEntryCount(Product $product): int
    {
        return RaffleEntry::pending()->forProduct($product)->count();
    }

    public function setWinner(RaffleEntry $raffleEntry): void
    {
        $raffleEntry->update([
            'status' => RaffleStatus::WINNER->value,
        ]);
    }

    /**
     * @return array<int>
     */
    public function randomlySelectWinners(Product $product): array
    {
        return RaffleEntry::pending()
            ->forProduct($product)
            ->inRandomOrder()
            ->take($product->qty)
            ->pluck('id')
            ->toArray();
    }
}
