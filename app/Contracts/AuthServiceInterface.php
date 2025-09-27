<?php

namespace App\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(array $data): array;

    public function login(array $credentials): array;

    public function me(): User;

    public function refresh(): array;

    public function logout(): bool;
}
