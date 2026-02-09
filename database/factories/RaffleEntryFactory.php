<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\RaffleStatus;
use App\Models\Address;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RaffleEntry>
 */
final class RaffleEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(RaffleStatus::cases()),
            'product_id' => Product::active()->inRandomOrder()?->value('id') ?? Product::factory(),
            'user_id' => User::factory(),
            'address_id' => fn (array $attributes) => Address::ofUserId($attributes['user_id'])->value('id'),
            'encrypted_payment_token' => fake()->iban(),
            'entry_code' => fake()->uuid(),
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'product_id' => $product->id,
            ];
        });
    }

    public function forUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RaffleStatus::PENDING->value,
            ];
        });
    }

    public function winner(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RaffleStatus::WINNER->value,
            ];
        });
    }

    public function loser(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RaffleStatus::LOSER->value,
            ];
        });
    }
}
