<?php

namespace app\Interfaces;

interface AuthenticatorInterface
{
    public function attempt(array $credentials): bool;
}
