<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\SelectWinnersAction;
use App\Enum\RaffleStatus;
use App\Models\Product;
use App\Models\RaffleEntry;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

final class SelectWinnersActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_select_same_number_of_winners_as_the_available_stock_qty_for_the_product(): void
    {
        // Arrange
        Bus::fake();

        $stockQty = 2;
        $product = Product::factory()->active()->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => $stockQty,
        ]);

        RaffleEntry::factory()
            ->pending()
            ->forProduct($product)
            ->count(10)
            ->create();

        // Act
        resolve(SelectWinnersAction::class)->handle($product->id);

        // Assert
        $batchName = sprintf('charge_raffle_winners_%s', $product->id);

        Bus::assertBatched(function (PendingBatch $batch) use ($batchName, $stockQty) {
            return $batch->name === $batchName
                && $batch->jobs->count() === $stockQty
                && isset($batch->jobs->first()->raffleEntryId);
        });
    }

    public function test_it_doesnt_select_winners_when_product_doesnt_have_any_stock_qty_available(): void
    {
        // Arrange
        Bus::fake();

        $product = Product::factory()->active()->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => 0,
        ]);

        RaffleEntry::factory()
            ->pending()
            ->forProduct($product)
            ->count(10)
            ->create();

        // Pre-Act Assert
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'qty' => 0,
        ]);

        // Act
        resolve(SelectWinnersAction::class)->handle($product->id);

        // Assert
        Bus::assertNothingBatched();
    }

    public function test_it_doesnt_select_winners_when_it_doesnt_have_any_pending_entries_available(): void
    {
        // Arrange
        Bus::fake();

        $product = Product::factory()->active()->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => 5,
        ]);

        // Pre-Act Assert
        $this->assertDatabaseMissing('raffle_entries', [
            'product_id' => $product->id,
            'status' => RaffleStatus::PENDING->value,
        ]);

        // Act
        resolve(SelectWinnersAction::class)->handle($product->id);

        // Assert
        Bus::assertNothingBatched();
    }
}
