<?php

namespace Middlewares;

use Model\User;
use Src\Auth\Auth;
use Src\Request;
use Src\View;

class RoleMiddleware
{
    public function handle(Request $request, ?string $role = null): void
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                (new View())->toJSON([
                    'message' => 'Пользователь не авторизован.',
                ], 401);
            }

            app()->route->redirect('/login');
            $request->stop();
            return;
        }

        $user = Auth::user();
        if (!$user instanceof User) {
            if ($request->expectsJson()) {
                (new View())->toJSON([
                    'message' => 'Пользователь не найден.',
                ], 401);
            }

            app()->route->redirect('/login');
            $request->stop();
            return;
        }

        if ($role === 'reader' && !$user->isReader()) {
            if ($request->expectsJson()) {
                (new View())->toJSON([
                    'message' => 'Доступ разрешён только читателю.',
                ], 403);
            }

            app()->route->redirect('/storage');
            $request->stop();
            return;
        }

        if ($role === 'librarian' && !$user->isLibrarian()) {
            if ($request->expectsJson()) {
                (new View())->toJSON([
                    'message' => 'Доступ разрешён только библиотекарю.',
                ], 403);
            }

            app()->route->redirect('/catalog');
            $request->stop();
        }
    }
}
