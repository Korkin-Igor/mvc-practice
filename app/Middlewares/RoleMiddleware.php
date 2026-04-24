<?php

namespace Middlewares;

use Model\User;
use Src\Auth\Auth;
use Src\Request;

class RoleMiddleware
{
    public function handle(Request $request, ?string $role = null): void
    {
        if (!Auth::check()) {
            app()->route->redirect('/login');
            $request->stop();
            return;
        }

        $user = Auth::user();
        if (!$user instanceof User) {
            app()->route->redirect('/login');
            $request->stop();
            return;
        }

        if ($role === 'reader' && !$user->isReader()) {
            app()->route->redirect('/storage');
            $request->stop();
            return;
        }

        if ($role === 'librarian' && !$user->isLibrarian()) {
            app()->route->redirect('/catalog');
            $request->stop();
        }
    }
}
