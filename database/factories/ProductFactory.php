<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
final class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'slug' => fn (array $attributes) => Str::slug($attributes['name'], '-'),
            'status' => ProductStatus::ACTIVE->value,
            'brand_id' => Brand::inRandomOrder()->value('id') ?? Brand::factory(),
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'sku' => fake()->unique()->bothify('????-######'),
            'price' => fake()->randomElement([199.99, 299.99, 399.99]),
            'description' => fake()->realText($maxNbChars = 200, $indexSize = 1),
            'image_url' => new Uri('https://placehold.co/600x400/png'),
            'qty' => fake()->randomNumber(2, true),
            'raffle_date' => fake()->dateTimeThisMonth('+12 days'),
        ];
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ProductStatus::ACTIVE->value,
                'raffle_date' => now()->addDays(rand(1, 5)),
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ProductStatus::INACTIVE->value,
                'raffle_date' => now()->addDays(rand(10, 15)),
            ];
        });
    }

    public function raffled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ProductStatus::RAFFLED->value,
                'raffle_date' => now()->subDays(rand(1, 5)),
            ];
        });
    }
}
