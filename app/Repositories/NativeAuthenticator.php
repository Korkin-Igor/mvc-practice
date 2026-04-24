<?php

namespace app\Repositories;

use app\Interfaces\AuthenticatorInterface;
use Src\Auth\Auth;

class NativeAuthenticator implements AuthenticatorInterface
{
    public function attempt(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }
}
