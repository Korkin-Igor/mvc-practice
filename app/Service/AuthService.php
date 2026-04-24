<?php

namespace Service;

use Model\User;
use Src\Auth\Auth;
use Throwable;

class AuthService
{
    public function registerReader(string $name, string $login, string $password): OperationResult
    {
        if ($name === '' || $login === '' || $password === '') {
            return OperationResult::failure('Заполните имя, логин и пароль.');
        }

        try {
            if (User::where('login', $login)->exists()) {
                return OperationResult::failure('Пользователь с таким логином уже существует.');
            }

            User::create([
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
        if (Auth::attempt($credentials)) {
            return OperationResult::success();
        }

        return OperationResult::failure('Неправильный логин или пароль.');
    }
}
