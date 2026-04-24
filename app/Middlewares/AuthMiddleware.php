<?php

namespace Middlewares;

use Src\Auth\Auth;
use Src\Request;
use Src\View;

class AuthMiddleware
{
    public function handle(Request $request, ?string $argument = null): void
    {
        if ($request->isApi() && !$request->bearerToken()) {
            (new View())->toJSON([
                'message' => 'Для доступа к API требуется Bearer token.',
            ], 401);
        }

        if (!Auth::check()) {
            if ($request->expectsJson()) {
                (new View())->toJSON([
                    'message' => 'Пользователь не авторизован.',
                ], 401);
            }

            app()->route->redirect('/login');
            $request->stop();
        }
    }
}
