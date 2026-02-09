<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_product_list_when_active_products_are_available(): void
    {
        // Arrange
        Product::factory()->active()->count(10)->create();

        // Act
        $response = $this->get('/api/products');

        // Assert
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 6, function (AssertableJson $json) {
                    $json->has('name')
                        ->has('brand')
                        ->has('category')
                        ->has('slug')
                        ->has('sku')
                        ->has('raffle_date')
                        ->has('image_url');
                })
                    ->has('links')
                    ->has('meta');
            });
    }

    public function test_it_returns_empty_list_when_no_products_are_active(): void
    {
        // Arrange
        Product::factory()->inactive()->count(5)->create();
        Product::factory()->raffled()->count(5)->create();

        // Act
        $response = $this->get('/api/products');

        // Assert
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 0)
                    ->has('links')
                    ->has('meta');
            });
    }

    public function test_it_returns_product_detail_when_product_is_active(): void
    {
        // Arrange
        $slug = 'testing-slug';
        Product::factory()->active()->create([
            'slug' => $slug,
        ]);

        // Act
        $response = $this->get('/api/products/'.$slug);

        // Assert
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('name')
                    ->has('brand')
                    ->has('category')
                    ->has('slug')
                    ->has('sku')
                    ->has('price')
                    ->has('description')
                    ->has('raffle_date')
                    ->has('image_url');
            });
    }

    public function test_it_returns_not_found_response_when_product_is_not_active(): void
    {
        // Arrange
        $slug = 'testing-slug';
        Product::factory()->inactive()->create([
            'slug' => $slug,
        ]);

        // Act
        $response = $this->get('/api/products/'.$slug);

        // Assert
        $response->assertNotFound()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->where('error', 'Product not found.');
            });
    }

    public function test_it_returns_not_found_response_when_product_is_not_found(): void
    {
        // Arrange
        $slug = 'testing-slug';

        // Act
        $response = $this->get('/api/products/'.$slug);

        // Assert
        $response->assertNotFound()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->where('error', 'Product not found.');
            });
    }
}
