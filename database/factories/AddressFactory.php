<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\CountryCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
final class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'post_code' => fake()->postcode(),
            'country_code' => fake()->randomElement(CountryCode::cases()),
            'contact_number' => fake()->phoneNumber(),
        ];
    }

    public function forUser(User $user): Factory
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
