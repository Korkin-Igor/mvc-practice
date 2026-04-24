<?php

namespace Service\Contracts;

interface AuthenticatorInterface
{
    public function attempt(array $credentials): bool;
}
