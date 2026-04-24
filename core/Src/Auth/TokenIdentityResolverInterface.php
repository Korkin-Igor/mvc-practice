<?php

namespace Src\Auth;

interface TokenIdentityResolverInterface
{
    public function resolveIdentityByToken(string $token);
}
