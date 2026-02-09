<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Enum\OrderStatus;
use App\Enum\ProductStatus;
use App\Enum\RaffleStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\RaffleEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StartRaffleCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_orders_for_winner_entries_and_update_raffle_entry_status_and_decement_product_stock_qty(): void
    {
        // Arrange
        $product = Product::factory()->active()->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => 2,
        ]);

        RaffleEntry::factory()
            ->pending()
            ->forProduct($product)
            ->count($product->qty + 5)
            ->create();

        // Pre-Act Assert
        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseMissing('raffle_entries', [
            'product_id' => $product->id,
            'status' => RaffleStatus::WINNER->value,
        ]);

        // Act
        $this->artisan('app:start-raffle');

        // Assert Orders are created
        $this->assertDatabaseCount('orders', $product->qty);

        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'status' => OrderStatus::COMPLETED->value,
        ]);

        // Assert winning Entries' status was updated to winner
        $winnerEntryIds = Order::forProduct($product)->pluck('raffle_entry_id')->toArray();

        foreach ($winnerEntryIds as $winnerId) {
            $this->assertDatabaseHas('raffle_entries', [
                'id' => $winnerId,
                'product_id' => $product->id,
                'status' => RaffleStatus::WINNER->value,
            ]);
        }

        // Assert Product qty was decremented
        $this->assertEquals(0, Product::find($product->id)->value('qty'));
    }

    public function test_it_can_process_multiple_raffles_at_once(): void
    {
        // Arrange
        $product1 = Product::factory()->active()->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => 2,
        ]);

        $product2 = Product::factory()->active()->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => 2,
        ]);

        RaffleEntry::factory()
            ->pending()
            ->forProduct($product1)
            ->count($product1->qty + 5)
            ->create();

        RaffleEntry::factory()
            ->pending()
            ->forProduct($product2)
            ->count($product2->qty + 5)
            ->create();

        // Pre-Act Assert
        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseMissing('raffle_entries', [
            'product_id' => $product1->id,
            'status' => RaffleStatus::WINNER->value,
        ]);

        $this->assertDatabaseMissing('raffle_entries', [
            'product_id' => $product2->id,
            'status' => RaffleStatus::WINNER->value,
        ]);

        // Act
        $this->artisan('app:start-raffle');

        // Assert Orders are created
        $this->assertDatabaseCount('orders', $product1->qty + $product2->qty);

        // Assert Orders are created for Product1 raffle
        $this->assertDatabaseHas('orders', [
            'product_id' => $product1->id,
            'status' => OrderStatus::COMPLETED->value,
        ]);

        // Assert winning Entries' status was updated to winner for Product1 raffle
        $winnerEntryIds1 = Order::forProduct($product1)->pluck('raffle_entry_id')->toArray();

        foreach ($winnerEntryIds1 as $winnerId) {
            $this->assertDatabaseHas('raffle_entries', [
                'id' => $winnerId,
                'product_id' => $product1->id,
                'status' => RaffleStatus::WINNER->value,
            ]);
        }

        // Assert Product1 qty was decremented
        $this->assertEquals(0, Product::find($product1->id)->value('qty'));

        // Assert Orders are created for Product2 raffle
        $this->assertDatabaseHas('orders', [
            'product_id' => $product2->id,
            'status' => OrderStatus::COMPLETED->value,
        ]);

        // Assert winning Entries' status was updated to winner for Product2 raffle
        $winnerEntryIds2 = Order::forProduct($product2)->pluck('raffle_entry_id')->toArray();

        foreach ($winnerEntryIds2 as $winnerId) {
            $this->assertDatabaseHas('raffle_entries', [
                'id' => $winnerId,
                'product_id' => $product2->id,
                'status' => RaffleStatus::WINNER->value,
            ]);
        }

        // Assert Product2 qty was decremented
        $this->assertEquals(0, Product::find($product2->id)->value('qty'));
    }

    public function test_it_doesnt_start_raffle_when_no_product_are_eligable_for_raffle(): void
    {
        // Arrange
        $stockQty = 2;
        // date has not passed to be eligable
        Product::factory()->active()->count(2)->create([
            'raffle_date' => now()->addDays(2),
            'qty' => $stockQty,
        ]);

        // inactive product
        Product::factory()->inactive()->count(2)->create([
            'raffle_date' => now()->addDays(2),
            'qty' => $stockQty,
        ]);

        $products = Product::cursor();

        foreach ($products as $product) {
            RaffleEntry::factory()
                ->pending()
                ->forProduct($product)
                ->count(5)
                ->create();
        }

        // Pre-Act Assert
        $this->assertDatabaseCount('orders', 0);

        // Act
        $this->artisan('app:start-raffle');

        // Assert
        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseMissing('raffle_entries', [
            'status' => RaffleStatus::WINNER->value,
        ]);

        foreach ($products as $product) {
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'qty' => $stockQty,
            ]);
        }
    }

    public function test_it_stops_raffle_when_a_product_doesnt_have_stock_qty(): void
    {
        // Arrange
        $products = Product::factory()->active()->count(2)->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => 0,
        ]);

        foreach ($products as $product) {
            RaffleEntry::factory()
                ->pending()
                ->forProduct($product)
                ->count(5)
                ->create();
        }

        // Pre-Act Assert
        $this->assertDatabaseCount('orders', 0);

        // Act
        $this->artisan('app:start-raffle');

        // Assert
        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseMissing('raffle_entries', [
            'status' => RaffleStatus::WINNER->value,
        ]);

        foreach ($products as $product) {
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'status' => ProductStatus::ACTIVE->value,
                'qty' => 0,
            ]);
        }
    }

    public function test_it_stops_raffle_when_it_doesnt_have_any_pending_entries(): void
    {
        // Arrange
        $stockQty = 2;
        $product = Product::factory()->active()->create([
            'raffle_date' => now()->subMinutes(5),
            'qty' => $stockQty,
        ]);

        // Pre-Act Assert
        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseMissing('raffle_entries', [
            'status' => RaffleStatus::PENDING->value,
        ]);

        // Act
        $this->artisan('app:start-raffle');

        // Assert
        $this->assertDatabaseCount('orders', 0);

        $this->assertDatabaseMissing('raffle_entries', [
            'status' => RaffleStatus::WINNER->value,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => ProductStatus::ACTIVE->value,
            'qty' => $stockQty,
        ]);
    }
}
