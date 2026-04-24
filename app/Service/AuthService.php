<?php

namespace Service;

use Model\User;
use Service\Contracts\AuthenticatorInterface;
use Service\Contracts\AuthUserRepositoryInterface;
use Service\Gateways\EloquentAuthUserRepository;
use Service\Gateways\NativeAuthenticator;
use Src\Auth\Auth;
use Throwable;

class AuthService
{
    private AuthUserRepositoryInterface $users;
    private AuthenticatorInterface $authenticator;

    public function __construct(
        ?AuthUserRepositoryInterface $users = null,
        ?AuthenticatorInterface $authenticator = null
    ) {
        $this->users = $users ?? new EloquentAuthUserRepository();
        $this->authenticator = $authenticator ?? new NativeAuthenticator();
    }

    public function registerReader(string $name, string $login, string $password): OperationResult
    {
        if ($name === '' || $login === '' || $password === '') {
            return OperationResult::failure('Заполните имя, логин и пароль.');
        }

        try {
            if ($this->users->loginExists($login)) {
                return OperationResult::failure('Пользователь с таким логином уже существует.');
            }

            $this->users->create([
                'name' => $name,
                'login' => $login,
                'password' => $password,
                'role_id' => User::ROLE_READER,
            ]);

            return OperationResult::success('Регистрация завершена. Теперь можно войти в кабинет читателя.');
        } catch (Throwable $exception) {
            return OperationResult::failure('Не удалось зарегистрировать пользователя. Проверьте структуру таблиц и подключение к БД.');
        }
    }

    public function attemptLogin(array $credentials): OperationResult
    {
        if ($this->authenticator->attempt($credentials)) {
            return OperationResult::success();
        }

        return OperationResult::failure('Неправильный логин или пароль.');
    }
}
