<?php

namespace App\Services;

use App\Contracts\AuthRepositoryInterface;
use App\Contracts\AuthServiceInterface;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private AuthRepositoryInterface $authRepository
    ) {}

    public function register(array $data): array
    {
        $user = $this->authRepository->createUser($data);
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    public function login(array $credentials): array
    {
        if (! $token = JWTAuth::attempt($credentials)) {
            throw new \Exception('Invalid credentials');
        }

        $user = auth('api')->user();

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    public function me(): User
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user) {
            throw new \Exception('User not found');
        }

        return $user;
    }

    public function refresh(): array
    {
        $token = JWTAuth::refresh();

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    public function logout(): bool
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return true;
    }
}
