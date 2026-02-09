<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateRaffleEntryAction;
use App\DTOs\RaffleEntryDTO;
use App\Enum\RaffleStatus;
use App\Events\RaffleEntryCreated;
use App\Models\Address;
use App\Models\Product;
use App\Models\RaffleEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CreateRaffleEntryActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Address $address;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Arrange
        Event::fake();

        $this->user = User::factory()->create();
        $this->address = Address::ofUserId($this->user->id)->first();
        $this->product = Product::factory()->active()->create();
    }

    public function test_it_creates_raffle_entry_and_return_entry_object(): void
    {
        // Arrange
        $raffleEntryDTO = new RaffleEntryDTO(
            user: $this->user,
            address: $this->address,
            product: $this->product,
            status: RaffleStatus::PENDING,
            paymentToken: Str::uuid()->toString(),
        );

        // Pre-Act Assert
        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // Act
        $raffleEntry = resolve(CreateRaffleEntryAction::class)->handle($raffleEntryDTO);

        // Assert
        $this->assertTrue($raffleEntry->wasRecentlyCreated);

        $this->assertInstanceOf(RaffleEntry::class, $raffleEntry);
        $this->assertEquals($this->user->id, $raffleEntry->user_id);
        $this->assertEquals($this->product->id, $raffleEntry->product_id);

        $this->assertDatabaseHas('raffle_entries', [
            'id' => $raffleEntry->id,
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        Event::assertDispatchedOnce(RaffleEntryCreated::class);
    }

    public function test_it_doesnt_create_new_entry_if_user_has_already_entered_the_raffle_and_return_existing_entry_object(): void
    {
        // Arrange
        $raffleEntryAlready = RaffleEntry::factory()
            ->pending()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create();

        $raffleEntryDTO = new RaffleEntryDTO(
            user: $this->user,
            address: $this->address,
            product: $this->product,
            status: RaffleStatus::PENDING,
            paymentToken: Str::uuid()->toString(),
        );

        // Pre-Act Assert
        $this->assertDatabaseHas('raffle_entries', [
            'id' => $raffleEntryAlready->id,
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // Act
        $raffleEntry = resolve(CreateRaffleEntryAction::class)->handle($raffleEntryDTO);

        // Assert
        $this->assertFalse($raffleEntry->wasRecentlyCreated);

        $this->assertInstanceOf(RaffleEntry::class, $raffleEntry);
        $this->assertEquals($this->user->id, $raffleEntry->user_id);
        $this->assertEquals($this->product->id, $raffleEntry->product_id);

        Event::assertNotDispatched(RaffleEntryCreated::class);
    }
}
