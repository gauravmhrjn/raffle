<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\LoginAttemptAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LoginAttemptActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Arrange
        User::factory()->withoutCallbacks()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_it_returns_user_object_when_user_credentials_are_authenticated(): void
    {
        // Act
        $user = resolve(LoginAttemptAction::class)->handle('test@example.com', 'password');

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_it_returns_false_when_user_credentails_doesnt_match(): void
    {
        // Act
        $user = resolve(LoginAttemptAction::class)->handle('test@example.com', 'incorrect');

        // Assert
        $this->assertFalse($user);
    }
}
