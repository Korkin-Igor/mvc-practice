<?php

namespace Service;

use app\Interfaces\ApiTokenRepositoryInterface;
use app\Repositories\EloquentApiTokenRepository;
use DateTimeImmutable;
use Model\User;
use Src\Auth\TokenIdentityResolverInterface;
use Throwable;

class ApiTokenService implements TokenIdentityResolverInterface
{
    private ApiTokenRepositoryInterface $tokens;

    public function __construct(?ApiTokenRepositoryInterface $tokens = null)
    {
        $this->tokens = $tokens ?? new EloquentApiTokenRepository();
    }

    public function issueForUser(User $user): ?string
    {
        try {
            $plainToken = bin2hex(random_bytes(32));
            $timestamp = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

            $this->tokens->deleteForUser((int) $user->id);
            $this->tokens->create([
                'user_id' => (int) $user->id,
                'token' => $this->hashToken($plainToken),
                'created_at' => $timestamp,
                'last_used_at' => $timestamp,
            ]);

            return $plainToken;
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function resolveIdentityByToken(string $token)
    {
        if ($token === '') {
            return null;
        }

        try {
            $tokenHash = $this->hashToken($token);
            $user = $this->tokens->findIdentityByToken($tokenHash);
            if ($user) {
                $this->tokens->touchByToken(
                    $tokenHash,
                    (new DateTimeImmutable('now'))->format('Y-m-d H:i:s')
                );
            }

            return $user;
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function revoke(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        try {
            return $this->tokens->deleteByToken($this->hashToken($token));
        } catch (Throwable $exception) {
            return false;
        }
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
