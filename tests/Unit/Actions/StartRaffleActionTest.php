<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\StartRaffleAction;
use App\Jobs\SelectWinnersJob;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class StartRaffleActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Arrange
        Queue::fake();

        Product::factory()->inactive()->count(2)->create();
        Product::factory()->raffled()->count(2)->create();
    }

    public function test_it_generate_same_number_of_queued_jobs_equal_to_number_of_products_available_for_raffle(): void
    {
        // Arrange
        $activeProductCount = 2;
        $products = Product::factory()->active()->count($activeProductCount)->create([
            'raffle_date' => now()->subMinutes(5),
        ]);

        // Act
        resolve(StartRaffleAction::class)->handle();

        // Assert
        Queue::assertPushedTimes(SelectWinnersJob::class, $activeProductCount);

        Queue::assertPushed(function (SelectWinnersJob $job) use ($products) {
            return $job->productId === $products->first()->id;
        });
    }

    public function test_it_doesnt_generate_queued_jobs_when_it_couldnt_find_any_product_available_for_raffle(): void
    {
        // Arrange
        Product::factory()->active()->count(2)->create([
            'raffle_date' => now()->addDays(2),
        ]);

        // Act
        resolve(StartRaffleAction::class)->handle();

        // Assert
        Queue::assertNotPushed(SelectWinnersJob::class);
    }
}
