<?php

namespace Src;

use Error;

class Settings
{
    private array $_settings;

    public function __construct(array $settings = [])
    {
        $this->_settings = $settings;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->_settings)) {
            return $this->_settings[$key];
        }
        throw new Error('Accessing a non-existent property');
    }

    public function getRootPath(): string
    {
        $root = $this->path['root'] ?? '';
        return $root ? '/' . $root : '';
    }

    public function getViewsPath(): string
    {
        $path = $this->path['views'] ?? '';
        return $path ? '/' . $path : '';
    }

    public function getRoutePath(): string
    {
        $path = $this->path['routes'] ?? '';
        return $path ? '/' . $path : '';
    }

    public function getDbSetting(): array
    {
        return $this->db ?? [];
    }

    public function getAuthClassName(): string
    {
        return $this->app['auth'] ?? '';
    }

    public function getIdentityClassName(): string
    {
        return $this->app['identity'] ?? '';
    }

    public function removeAppMiddleware(string $key): void
    {
        unset($this->_settings['app']['routeAppMiddleware'][$key]);
    }
}
