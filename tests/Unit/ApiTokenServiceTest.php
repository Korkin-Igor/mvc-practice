<?php

declare(strict_types=1);

namespace Tests\Unit;

use app\Interfaces\ApiTokenRepositoryInterface;
use Service\ApiTokenService;
use Tests\TestCase;

final class ApiTokenServiceTest extends TestCase
{
    public function testIssueForUserReplacesOldTokenAndStoresHash(): void
    {
        $user = $this->makeUser(['id' => 10]);
        $storedHash = null;

        $tokens = $this->createMock(ApiTokenRepositoryInterface::class);
        $tokens->expects($this->once())
            ->method('deleteForUser')
            ->with(10);
        $tokens->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $payload) use (&$storedHash): bool {
                $storedHash = $payload['token'] ?? null;

                return (int) ($payload['user_id'] ?? 0) === 10
                    && is_string($storedHash)
                    && strlen($storedHash) === 64
                    && !empty($payload['created_at'])
                    && !empty($payload['last_used_at']);
            }));

        $service = new ApiTokenService($tokens);
        $plainToken = $service->issueForUser($user);

        $this->assertNotNull($plainToken);
        $this->assertSame(64, strlen($plainToken));
        $this->assertSame(hash('sha256', $plainToken), $storedHash);
        $this->assertNotSame($plainToken, $storedHash);
    }

    public function testResolveIdentityByTokenReturnsUserAndTouchesTimestamp(): void
    {
        $user = $this->makeUser(['id' => 15]);
        $plainToken = str_repeat('a', 64);
        $tokenHash = hash('sha256', $plainToken);

        $tokens = $this->createMock(ApiTokenRepositoryInterface::class);
        $tokens->expects($this->once())
            ->method('findIdentityByToken')
            ->with($tokenHash)
            ->willReturn($user);
        $tokens->expects($this->once())
            ->method('touchByToken')
            ->with(
                $tokenHash,
                $this->callback(static fn (string $timestamp): bool => $timestamp !== '')
            );

        $service = new ApiTokenService($tokens);

        $this->assertSame($user, $service->resolveIdentityByToken($plainToken));
    }

    public function testRevokeDeletesTokenByHash(): void
    {
        $plainToken = str_repeat('b', 64);
        $tokenHash = hash('sha256', $plainToken);

        $tokens = $this->createMock(ApiTokenRepositoryInterface::class);
        $tokens->expects($this->once())
            ->method('deleteByToken')
            ->with($tokenHash)
            ->willReturn(true);

        $service = new ApiTokenService($tokens);

        $this->assertTrue($service->revoke($plainToken));
    }
}
