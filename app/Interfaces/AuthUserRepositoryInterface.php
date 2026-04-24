<?php

namespace app\Interfaces;

interface AuthUserRepositoryInterface
{
    public function loginExists(string $login): bool;

    public function create(array $attributes): void;
}
