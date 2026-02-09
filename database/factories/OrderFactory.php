<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Models\Address;
use App\Models\Product;
use App\Models\RaffleEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
final class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_code' => fake()->uuid(),
            'payment_transaction_code' => fake()->uuid(),
            'status' => OrderStatus::COMPLETED->value,
            'payment_status' => PaymentStatus::SUCCESS->value,
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'address_id' => fn (array $attributes) => Address::ofUserId($attributes['user_id'])->inRandomOrder()->value('id'),
            'product_id' => Product::active()->inRandomOrder()?->value('id') ?? Product::factory(),
            'raffle_entry_id' => function (array $attributes) {
                $user = User::find($attributes['user_id']);
                $product = Product::find($attributes['product_id']);

                return RaffleEntry::forUser($user)->forProduct($product)?->value('id')
                    ?? RaffleEntry::factory()->forUser($user)->forProduct($product)->create()->id;
            },
            'amount' => fn (array $attributes) => Product::find($attributes['product_id'])->value('price'),
        ];
    }

    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => OrderStatus::PENDING->value,
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => OrderStatus::COMPLETED->value,
            ];
        });
    }

    public function forRaffleEntry(RaffleEntry $raffleEntry): static
    {
        return $this->state(function (array $attributes) use ($raffleEntry) {
            return [
                'raffle_entry_id' => $raffleEntry->id,
                'user_id' => $raffleEntry->user_id,
                'address_id' => $raffleEntry->address_id,
                'product_id' => $raffleEntry->product_id,
            ];
        });
    }
}
