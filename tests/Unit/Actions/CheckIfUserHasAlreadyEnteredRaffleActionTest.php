<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CheckIfUserHasAlreadyEnteredRaffleAction;
use App\Models\Product;
use App\Models\RaffleEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckIfUserHasAlreadyEnteredRaffleActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Arrange
        RaffleEntry::factory()->pending()->count(2)->create();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->active()->create();
    }

    public function test_it_returns_false_when_a_user_has_not_entered_the_raffle(): void
    {
        // Act
        $raffleEntry = resolve(CheckIfUserHasAlreadyEnteredRaffleAction::class)->handle($this->user, $this->product);

        // Assert
        $this->assertFalse($raffleEntry);
    }

    public function test_it_can_detect_if_a_user_has_already_entered_the_raffle(): void
    {
        // Arrange
        RaffleEntry::factory()
            ->pending()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create();

        // Act
        $raffleEntry = resolve(CheckIfUserHasAlreadyEnteredRaffleAction::class)->handle($this->user, $this->product);

        // Assert
        $this->assertInstanceOf(RaffleEntry::class, $raffleEntry);
        $this->assertEquals($this->user->id, $raffleEntry->user_id);
        $this->assertEquals($this->product->id, $raffleEntry->product_id);
    }
}
