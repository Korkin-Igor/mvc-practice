<?php

namespace Src;

use Error;

class Request
{
    protected array $body;
    protected bool $stopped = false;
    public string $method;
    public array $headers;

    public function __construct()
    {
        $this->body = $_REQUEST;
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->headers = $this->normalizeHeaders();
    }

    public function all(): array
    {
        return $this->body + $this->files();
    }

    public function set($field, $value): void
    {
        $this->body[$field] = $value;
    }

    public function get($field)
    {
        return $this->body[$field] ?? null;
    }

    public function files(): array
    {
        return $_FILES;
    }

    public function header(string $name, $default = null)
    {
        $key = mb_strtolower($name, 'UTF-8');
        return $this->headers[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = trim((string) $this->header('Authorization', ''));
        if (stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($header, 7));
        return $token !== '' ? $token : null;
    }

    public function uri(): string
    {
        return (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    }

    public function isApi(): bool
    {
        $uri = $this->uri();
        if (function_exists('app')) {
            $root = app()->settings->getRootPath();
            if ($root && strpos($uri, $root) === 0) {
                $uri = substr($uri, strlen($root)) ?: '/';
            }
        }

        return strpos($uri, '/api') === 0;
    }

    public function expectsJson(): bool
    {
        $accept = (string) $this->header('Accept', '');
        $contentType = (string) $this->header('Content-Type', '');

        return $this->isApi()
            || strpos($accept, 'application/json') !== false
            || strpos($contentType, 'application/json') !== false;
    }

    public function stop(): void
    {
        $this->stopped = true;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }
        throw new Error('Accessing a non-existent property');
    }

    private function normalizeHeaders(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[mb_strtolower((string) $name, 'UTF-8')] = $value;
            }
        }

        foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION', 'CONTENT_TYPE', 'HTTP_ACCEPT'] as $key) {
            if (!isset($_SERVER[$key])) {
                continue;
            }

            $name = str_replace('_', '-', strtolower(preg_replace('/^(http_|redirect_http_)/i', '', $key)));
            $headers[$name] = $_SERVER[$key];
        }

        return $headers;
    }
}
