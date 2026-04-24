<?php

namespace Controller;

use Model\User;
use Src\Auth\Auth;
use Src\Request;
use Src\Session;
use Src\View;

abstract class BaseController
{
    protected function redirectIfAuthenticated(): ?string
    {
        $user = Auth::user();
        if ($user instanceof User) {
            return $this->redirect($this->defaultRouteFor($user));
        }

        return null;
    }

    protected function authenticatedUser(): ?User
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            app()->route->redirect('/login');
            return null;
        }

        return $user;
    }

    protected function input(Request $request, string $name, string $default = ''): string
    {
        $value = $request->all()[$name] ?? $default;
        if (!is_scalar($value)) {
            return $default;
        }

        return trim((string) $value);
    }

    protected function defaultRouteFor(User $user): string
    {
        return $user->isLibrarian() ? '/storage' : '/catalog';
    }

    protected function renderPage(string $view, array $data): string
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            $user = null;
        }

        return (new View())->render($view, array_merge([
            'appName' => 'Либрари',
            'flash' => $this->pullFlash(),
            'currentUser' => $user,
            'navItems' => $this->navigationFor($user),
        ], $data));
    }

    protected function flash(string $type, string $message): void
    {
        Session::set('flash', [
            'type' => $type,
            'message' => $message,
        ]);
    }

    protected function redirect(string $url): string
    {
        app()->route->redirect($url);
        return '';
    }

    private function pullFlash(): ?array
    {
        $flash = Session::get('flash');
        Session::clear('flash');
        return $flash;
    }

    private function navigationFor(?User $user): array
    {
        if (!$user) {
            return [];
        }

        if ($user->isLibrarian()) {
            return [
                ['id' => 'storage', 'label' => 'Хранилище книг', 'url' => '/storage'],
                ['id' => 'bookings', 'label' => 'Брони', 'url' => '/bookings'],
            ];
        }

        return [
            ['id' => 'catalog', 'label' => 'Каталог', 'url' => '/catalog'],
            ['id' => 'my-bookings', 'label' => 'Мои брони', 'url' => '/my-bookings'],
        ];
    }
}
