<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\LoginAttemptAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginAttemptAction $loginAttemptAction): JsonResponse
    {
        $user = $loginAttemptAction->handle(
            $request->string('email')->value(),
            $request->string('password')->value(),
        );

        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'You are logged in.',
                'token' => $user->createToken(
                    $request->string('email')->value()
                )->plainTextToken,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'status' => 'failed',
            'error' => [
                'credentials' => 'The provided credentials do not match our records.',
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'You have been logged out.',
        ], Response::HTTP_OK);
    }
}
