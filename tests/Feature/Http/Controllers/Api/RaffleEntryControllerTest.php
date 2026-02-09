<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Enum\ProductStatus;
use App\Enum\RaffleStatus;
use App\Exceptions\AddressNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Address;
use App\Models\Product;
use App\Models\RaffleEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class RaffleEntryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_the_raffle_entry_when_user_cancels_it(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->active()->create();

        $raffleEntry = RaffleEntry::factory()
            ->pending()
            ->forUser($user)
            ->forProduct($product)
            ->create();

        // Pre-Act Assert
        $this->assertDatabaseHas('raffle_entries', [
            'id' => $raffleEntry->id,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->delete('/api/raffle/entry/delete', [
            'product_id' => $product->id,
        ]);

        // Assert
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success');
            });

        $this->assertDatabaseMissing('raffle_entries', [
            'id' => $raffleEntry->id,
        ]);
    }

    public function test_it_returns_validation_error_when_product_is_not_active(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->inactive()->create();

        $raffleEntry = RaffleEntry::factory()
            ->pending()
            ->forUser($user)
            ->forProduct($product)
            ->create();

        // Pre-Act Assert
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => ProductStatus::INACTIVE->value,
        ]);

        $this->assertDatabaseHas('raffle_entries', [
            'id' => $raffleEntry->id,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->delete('/api/raffle/entry/delete', [
            'product_id' => 101,
        ]);

        // Assert
        $response->assertBadRequest()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->has('error');
            });
    }

    public function test_it_returns_validation_error_when_product_doesnt_exist(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Pre-Act Assert
        $this->assertDatabaseMissing('products', [
            'id' => 101,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->delete('/api/raffle/entry/delete', [
            'product_id' => 101, // product id doesnt exist
        ]);

        // Assert
        $response->assertBadRequest()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->has('error');
            });
    }

    public function test_it_can_create_raffle_entry(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address = Address::ofUserId($user->id)->first();

        $product = Product::factory()->active()->create([
            'raffle_date' => Carbon::now()->addDays(2),
            'qty' => 2,
        ]);

        // Pre-Act Assert
        $this->assertDatabaseMissing('raffle_entries', [
            'status' => RaffleStatus::PENDING->value,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->post('/api/raffle/entry', [
            'payment_token' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'address_id' => $address->id,
        ]);

        // Assert
        $response->assertCreated()
            ->assertJson(function (AssertableJson $json) use ($response) {
                $json->where('status', 'success')
                    ->where('entry_code', $response->json('entry_code'));
            });

        $this->assertDatabaseHas('raffle_entries', [
            'status' => RaffleStatus::PENDING->value,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'entry_code' => $response->json('entry_code'),
        ]);
    }

    public function test_it_doesnt_create_raffle_entry_when_raffle_already_exist(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address = Address::ofUserId($user->id)->first();

        $product = Product::factory()->active()->create([
            'raffle_date' => Carbon::now()->addDays(2),
            'qty' => 2,
        ]);

        RaffleEntry::factory()
            ->pending()
            ->forUser($user)
            ->forProduct($product)
            ->create();

        // Pre-Act Assert
        $this->assertDatabaseHas('raffle_entries', [
            'status' => RaffleStatus::PENDING->value,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->post('/api/raffle/entry', [
            'payment_token' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'address_id' => $address->id,
        ]);

        // Assert
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) use ($response) {
                $json->where('status', 'success')
                    ->where('entry_code', $response->json('entry_code'));
            });

        $this->assertDatabaseHas('raffle_entries', [
            'status' => RaffleStatus::PENDING->value,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'entry_code' => $response->json('entry_code'),
        ]);

        // Assert that its was not recently created
        $raffleEntry = RaffleEntry::forUser($user)->forProduct($product)->first();
        $this->assertFalse($raffleEntry->wasRecentlyCreated);
    }

    public function test_it_doesnt_create_raffle_entry_when_product_is_not_active(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address = Address::ofUserId($user->id)->first();

        $product = Product::factory()->inactive()->create([
            'raffle_date' => Carbon::now()->addDays(2),
            'qty' => 2,
        ]);

        // Pre-Act Assert
        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->post('/api/raffle/entry', [
            'payment_token' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'address_id' => $address->id,
        ]);

        // Assert
        $response->assertBadRequest()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->where('error', ProductNotFoundException::ERROR_MESSAGE);
            });

        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_it_doesnt_create_raffle_entry_when_product_doesnt_exist(): void
    {
        // Arrange
        $user = User::factory()->create();
        $address = Address::ofUserId($user->id)->first();

        // Pre-Act Assert
        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $user->id,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->post('/api/raffle/entry', [
            'payment_token' => Str::uuid()->toString(),
            'product_id' => 101, // product id doesnt exist
            'address_id' => $address->id,
        ]);

        // Assert
        $response->assertBadRequest()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->has('error');
            });

        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $user->id,
        ]);
    }

    public function test_it_doesnt_create_raffle_entry_when_address_doesnt_belong_to_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        $diffUser = User::factory()->create();
        $diffAddress = Address::ofUserId($diffUser->id)->first();

        $product = Product::factory()->active()->create([
            'raffle_date' => Carbon::now()->addDays(2),
            'qty' => 2,
        ]);

        // Pre-Act Assert
        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->post('/api/raffle/entry', [
            'payment_token' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'address_id' => $diffAddress->id, // address id doesnt belong to current user
        ]);

        // Assert
        $response->assertBadRequest()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->where('error', AddressNotFoundException::ERROR_MESSAGE);
            });

        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_it_doesnt_create_raffle_entry_when_address_doesnt_exist(): void
    {
        // Arrange
        $user = User::factory()->create();

        $product = Product::factory()->active()->create([
            'raffle_date' => Carbon::now()->addDays(2),
            'qty' => 2,
        ]);

        // Pre-Act Assert
        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Act
        Sanctum::actingAs($user);

        $response = $this->post('/api/raffle/entry', [
            'payment_token' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'address_id' => 101, // address id doesnt exist
        ]);

        // Assert
        $response->assertBadRequest()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->has('error');
            });

        $this->assertDatabaseMissing('raffle_entries', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}
