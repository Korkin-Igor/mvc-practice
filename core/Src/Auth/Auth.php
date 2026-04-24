<?php

namespace Src\Auth;

use Src\Session;

class Auth
{
    private static IdentityInterface $user;
    private static ?TokenIdentityResolverInterface $tokenResolver = null;

    public static function init(IdentityInterface $user): void
    {
        self::$user = $user;
    }

    public static function setTokenResolver(?TokenIdentityResolverInterface $resolver = null): void
    {
        self::$tokenResolver = $resolver;
    }

    public static function login(IdentityInterface $user): void
    {
        self::$user = $user;
        Session::set('id', self::$user->getId());
    }

    public static function attempt(array $credentials): bool
    {
        if ($user = self::$user->attemptIdentity($credentials)) {
            self::login($user);
            return true;
        }
        return false;
    }

    public static function user()
    {
        $token = self::bearerToken();
        if ($token !== null) {
            if (!self::$tokenResolver) {
                return null;
            }

            return self::$tokenResolver->resolveIdentityByToken($token);
        }

        $id = Session::get('id') ?? 0;
        return self::$user->findIdentity((int) $id);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function logout(): bool
    {
        Session::clear('id');
        return true;
    }

    public static function generateCSRF(): string
    {
        $token = Session::get('csrf_token');
        if ($token) {
            return $token;
        }

        $token = md5(uniqid((string) time(), true));
        Session::set('csrf_token', $token);
        return $token;
    }

    private static function bearerToken(): ?string
    {
        $header = '';

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if ($header === '') {
            $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        }

        if (stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($header, 7));
        return $token !== '' ? $token : null;
    }
}
