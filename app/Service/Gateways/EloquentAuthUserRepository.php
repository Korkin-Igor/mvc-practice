<?php

namespace Service\Gateways;

use Model\User;
use Service\Contracts\AuthUserRepositoryInterface;

class EloquentAuthUserRepository implements AuthUserRepositoryInterface
{
    public function loginExists(string $login): bool
    {
        return User::where('login', $login)->exists();
    }

    public function create(array $attributes): void
    {
        User::create($attributes);
    }
}
