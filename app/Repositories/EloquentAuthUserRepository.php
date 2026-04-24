<?php

namespace app\Repositories;

use app\Interfaces\AuthUserRepositoryInterface;
use Model\User;

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
