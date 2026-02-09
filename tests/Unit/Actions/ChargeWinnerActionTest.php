<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\ChargeWinnerAction;
use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Enum\RaffleStatus;
use App\Models\Product;
use App\Models\RaffleEntry;
use App\Services\PaymentServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

final class ChargeWinnerActionTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private RaffleEntry $raffleEntry;

    protected function setUp(): void
    {
        parent::setUp();

        // Arrange
        $this->product = Product::factory()->active()->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => 5,
        ]);

        $this->raffleEntry = RaffleEntry::factory()
            ->pending()
            ->forProduct($this->product)
            ->create();
    }

    public function test_it_creates_order_when_payment_processing_is_successful(): void
    {
        // Act
        resolve(ChargeWinnerAction::class)->handle($this->raffleEntry->id);

        // Assert
        $this->assertDatabaseHas('orders', [
            'raffle_entry_id' => $this->raffleEntry->id,
            'user_id' => $this->raffleEntry->user_id,
            'product_id' => $this->product->id,
            'amount' => $this->product->price,
            'status' => OrderStatus::COMPLETED->value,
            'payment_status' => PaymentStatus::SUCCESS->value,
        ]);
    }

    public function test_it_decrement_stock_qty_when_order_get_created(): void
    {
        // Pre-Act Assert
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'qty' => $this->product->qty,
        ]);

        // Act
        resolve(ChargeWinnerAction::class)->handle($this->raffleEntry->id);

        // Assert
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'qty' => $this->product->qty - 1,
        ]);
    }

    public function test_it_updates_status_of_pending_raffle_entry_to_winner_when_order_get_created(): void
    {
        // Pre-Act Assert
        $this->assertDatabaseHas('raffle_entries', [
            'id' => $this->raffleEntry->id,
            'status' => RaffleStatus::PENDING->value,
        ]);

        // Act
        resolve(ChargeWinnerAction::class)->handle($this->raffleEntry->id);

        // Assert
        $this->assertDatabaseHas('raffle_entries', [
            'id' => $this->raffleEntry->id,
            'status' => RaffleStatus::WINNER->value,
        ]);
    }

    public function test_it_doesnt_create_order_when_payment_processing_fails(): void
    {
        // Arrange
        $this->mock(PaymentServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('processMocked')
                ->once()
                ->andReturnFalse();
        });

        // Act
        resolve(ChargeWinnerAction::class)->handle($this->raffleEntry->id);

        // Assert
        $this->assertDatabaseMissing('orders', [
            'raffle_entry_id' => $this->raffleEntry->id,
        ]);
    }

    public function test_it_rolls_back_the_stock_qty_when_payment_processing_fails(): void
    {
        // Arrange
        $this->mock(PaymentServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('processMocked')
                ->once()
                ->andReturnFalse();
        });

        // Pre-Act Assert
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'qty' => $this->product->qty,
        ]);

        // Act
        resolve(ChargeWinnerAction::class)->handle($this->raffleEntry->id);

        // Assert
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'qty' => $this->product->qty,
        ]);
    }
}
