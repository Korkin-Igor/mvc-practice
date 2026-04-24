<?php

namespace Service\Gateways;

use Service\Contracts\AuthenticatorInterface;
use Src\Auth\Auth;

class NativeAuthenticator implements AuthenticatorInterface
{
    public function attempt(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }
}
