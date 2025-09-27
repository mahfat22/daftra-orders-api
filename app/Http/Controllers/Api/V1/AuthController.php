<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return Response::created([
                'user' => new UserResource($result['user']),
                'access_token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ], 'User registered successfully');
        } catch (\Exception $e) {
            return Response::error('Registration failed', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Authenticate user and return JWT token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return Response::success([
                'user' => new UserResource($result['user']),
                'access_token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ], 'Login successful');
        } catch (\Exception $e) {
            return Response::unauthorized('Invalid credentials', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get authenticated user.
     */
    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->me();

            return Response::success([
                'user' => new UserResource($user),
            ], 'User retrieved successfully');
        } catch (\Exception $e) {
            return Response::unauthorized('Could not authenticate user', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): JsonResponse
    {
        try {
            $result = $this->authService->refresh();

            return Response::success([
                'access_token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return Response::unauthorized('Could not refresh token', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Logout user and invalidate token.
     */
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();

            return Response::success(null, 'Successfully logged out');
        } catch (\Exception $e) {
            return Response::serverError('Could not log out user', ['error' => $e->getMessage()]);
        }
    }
}
