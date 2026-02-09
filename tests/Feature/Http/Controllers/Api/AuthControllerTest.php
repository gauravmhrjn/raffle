<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_token_when_user_successfully_login(): void
    {
        // Arrange
        $user = User::factory()->withoutCallbacks()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Act
        $response = $this->post('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Assert
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success')
                    ->where('message', 'You are logged in.')
                    ->has('token');
            });

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => $user->email,
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_it_doesnt_return_token_when_user_credentials_are_invalid(): void
    {
        // Arrange
        $user = User::factory()->withoutCallbacks()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Act
        $response = $this->post('/api/login', [
            'email' => 'test@example.com',
            'password' => 'incorrect', // wrong password
        ]);

        // Assert
        $response->assertUnauthorized()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->has('error');
            });

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_it_doesnt_return_token_when_validation_fails(): void
    {
        // Arrange
        $user = User::factory()->withoutCallbacks()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Act
        $response = $this->post('/api/login', [
            'email' => 'testexamplecom', // invalid email address
            'password' => 'password',
        ]);

        // Assert
        $response->assertBadRequest()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'failed')
                    ->has('error');
            });

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_it_logout_user_successfully(): void
    {
        // Arrange
        $user = User::factory()->withoutCallbacks()->create();

        // Act
        Sanctum::actingAs($user);

        $response = $this->post('/api/logout');

        // Assert
        $response->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success')
                    ->where('message', 'You have been logged out.');
            });

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
