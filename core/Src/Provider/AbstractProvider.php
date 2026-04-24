<?php

namespace Src\Provider;

use Src\Application;

abstract class AbstractProvider
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    abstract public function register(): void;

    abstract public function boot(): void;
}
