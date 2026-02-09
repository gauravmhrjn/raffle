<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CancelRaffleEntryAction;
use App\Models\Product;
use App\Models\RaffleEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CancelRaffleEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_delete_raffle_entry_when_entry_gets_cancel(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->active()->create();

        $raffleEntry = RaffleEntry::factory()
            ->pending()
            ->forUser($user)
            ->forProduct($product)
            ->create();

        // Pre-Act Assert
        $this->assertDatabaseHas('raffle_entries', [
            'id' => $raffleEntry->id,
            'user_id' => $raffleEntry->id,
            'product_id' => $raffleEntry->id,
        ]);

        // Act
        resolve(CancelRaffleEntryAction::class)->handle($user, $product);

        // Assert
        $this->assertDatabaseMissing('raffle_entries', [
            'id' => $raffleEntry->id,
        ]);
    }
}
