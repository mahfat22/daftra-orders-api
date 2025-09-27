<?php

namespace App\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function createUser(array $data): User;

    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;
}
