<?php

namespace app\Repositories;

use app\Interfaces\ApiTokenRepositoryInterface;
use Model\ApiToken;

class EloquentApiTokenRepository implements ApiTokenRepositoryInterface
{
    public function deleteForUser(int $userId): void
    {
        ApiToken::where('user_id', $userId)->delete();
    }

    public function create(array $attributes): void
    {
        ApiToken::create($attributes);
    }

    public function findIdentityByToken(string $tokenHash)
    {
        $token = ApiToken::with('user.role')
            ->where('token', $tokenHash)
            ->first();

        return $token ? $token->user : null;
    }

    public function touchByToken(string $tokenHash, string $timestamp): void
    {
        ApiToken::where('token', $tokenHash)->update([
            'last_used_at' => $timestamp,
        ]);
    }

    public function deleteByToken(string $tokenHash): bool
    {
        return ApiToken::where('token', $tokenHash)->delete() > 0;
    }
}
