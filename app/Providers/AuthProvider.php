<?php

namespace Providers;

use Service\ApiTokenService;
use Src\Provider\AbstractProvider;

class AuthProvider extends AbstractProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $authClass = $this->app->settings->getAuthClassName();
        $identityClass = $this->app->settings->getIdentityClassName();
        $tokenService = new ApiTokenService();

        $authClass::init(new $identityClass());
        $authClass::setTokenResolver($tokenService);
        $this->app->bind('tokenService', $tokenService);
        $this->app->bind('auth', new $authClass());
    }
}
