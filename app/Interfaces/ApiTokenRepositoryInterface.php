<?php

namespace app\Interfaces;

interface ApiTokenRepositoryInterface
{
    public function deleteForUser(int $userId): void;

    public function create(array $attributes): void;

    public function findIdentityByToken(string $tokenHash);

    public function touchByToken(string $tokenHash, string $timestamp): void;

    public function deleteByToken(string $tokenHash): bool;
}
