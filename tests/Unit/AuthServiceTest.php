<?php

declare(strict_types=1);

namespace Tests\Unit;

use Model\User;
use Service\AuthService;
use Service\Contracts\AuthenticatorInterface;
use Service\Contracts\AuthUserRepositoryInterface;
use Tests\TestCase;

final class AuthServiceTest extends TestCase
{
    public function testRegisterReaderCreatesReaderWhenLoginIsUnique(): void
    {
        $users = $this->createMock(AuthUserRepositoryInterface::class);
        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $users->expects($this->once())
            ->method('loginExists')
            ->with('reader_ivan')
            ->willReturn(false);

        $users->expects($this->once())
            ->method('create')
            ->with([
                'name' => 'Иван',
                'login' => 'reader_ivan',
                'password' => 'secret',
                'role_id' => User::ROLE_READER,
            ]);

        $service = new AuthService($users, $authenticator);
        $result = $service->registerReader('Иван', 'reader_ivan', 'secret');

        $this->assertTrue($result->isSuccess());
        $this->assertSame('Регистрация завершена. Теперь можно войти в кабинет читателя.', $result->getMessage());
    }

    public function testRegisterReaderRejectsDuplicateLogin(): void
    {
        $users = $this->createMock(AuthUserRepositoryInterface::class);
        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $users->expects($this->once())
            ->method('loginExists')
            ->with('reader_ivan')
            ->willReturn(true);

        $users->expects($this->never())->method('create');

        $service = new AuthService($users, $authenticator);
        $result = $service->registerReader('Иван', 'reader_ivan', 'secret');

        $this->assertFalse($result->isSuccess());
        $this->assertSame('Пользователь с таким логином уже существует.', $result->getMessage());
    }

    public function testRegisterReaderRejectsEmptyFields(): void
    {
        $users = $this->createStub(AuthUserRepositoryInterface::class);
        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $service = new AuthService($users, $authenticator);
        $result = $service->registerReader('', 'reader_ivan', 'secret');

        $this->assertFalse($result->isSuccess());
        $this->assertSame('Заполните имя, логин и пароль.', $result->getMessage());
    }

    public function testAttemptLoginReturnsSuccessWhenAuthenticatorAcceptsCredentials(): void
    {
        $users = $this->createStub(AuthUserRepositoryInterface::class);
        $authenticator = $this->createMock(AuthenticatorInterface::class);

        $authenticator->expects($this->once())
            ->method('attempt')
            ->with([
                'login' => 'reader_ivan',
                'password' => 'secret',
            ])
            ->willReturn(true);

        $service = new AuthService($users, $authenticator);
        $result = $service->attemptLogin([
            'login' => 'reader_ivan',
            'password' => 'secret',
        ]);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('', $result->getMessage());
    }

    public function testAttemptLoginReturnsFailureWhenAuthenticatorRejectsCredentials(): void
    {
        $users = $this->createStub(AuthUserRepositoryInterface::class);
        $authenticator = $this->createMock(AuthenticatorInterface::class);

        $authenticator->expects($this->once())
            ->method('attempt')
            ->willReturn(false);

        $service = new AuthService($users, $authenticator);
        $result = $service->attemptLogin([
            'login' => 'reader_ivan',
            'password' => 'wrong',
        ]);

        $this->assertFalse($result->isSuccess());
        $this->assertSame('Неправильный логин или пароль.', $result->getMessage());
    }
}
