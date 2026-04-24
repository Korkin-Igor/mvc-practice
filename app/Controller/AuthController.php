<?php

namespace Controller;

use Model\User;
use Service\AuthService;
use Src\Auth\Auth;
use Src\Request;
use Src\Validator\Validator;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function signup(Request $request): string
    {
        $redirect = $this->redirectIfAuthenticated();
        if ($redirect !== null) {
            return $redirect;
        }

        $message = '';
        if ($request->method === 'POST') {
            $validator = $this->signupValidator($request);
            if ($validator->fails()) {
                $message = $this->validationMessage($validator);
            } else {
                $result = $this->authService->registerReader(
                    $this->input($request, 'name'),
                    $this->input($request, 'login'),
                    $this->input($request, 'password')
                );

                if ($result->isSuccess()) {
                    $this->flash('success', $result->getMessage());
                    return $this->redirect('/login?role=reader');
                }

                $message = $result->getMessage();
            }
        }

        return $this->renderPage('site.signup', [
            'pageTitle' => 'Регистрация',
            'pageClass' => 'page-auth',
            'message' => $message,
        ]);
    }

    public function login(Request $request): string
    {
        $redirect = $this->redirectIfAuthenticated();
        if ($redirect !== null) {
            return $redirect;
        }

        $message = '';
        if ($request->method === 'POST') {
            $validator = $this->loginValidator($request);
            if ($validator->fails()) {
                $message = $this->validationMessage($validator);
            } else {
                $result = $this->authService->attemptLogin($request->all());
                if ($result->isSuccess()) {
                    $user = Auth::user();
                    if ($user instanceof User) {
                        return $this->redirect($this->defaultRouteFor($user));
                    }

                    return $this->redirect('/');
                }

                $message = $result->getMessage();
            }
        }

        return $this->renderPage('site.login', [
            'pageTitle' => 'Вход',
            'pageClass' => 'page-auth',
            'message' => $message,
            'preferredRole' => $this->input($request, 'role', 'reader'),
        ]);
    }

    public function logout(): string
    {
        Auth::logout();
        $this->flash('success', 'Сессия завершена.');
        return $this->redirect('/');
    }

    private function signupValidator(Request $request): Validator
    {
        return $this->validate($request->all(), [
            'name' => ['required'],
            'login' => ['required', 'unique:users,login'],
            'password' => ['required'],
        ], [
            'required' => 'Поле :field пусто',
            'unique' => 'Поле :field должно быть уникально',
        ]);
    }

    private function loginValidator(Request $request): Validator
    {
        return $this->validate($request->all(), [
            'login' => ['required'],
            'password' => ['required'],
        ], [
            'required' => 'Поле :field пусто',
        ]);
    }
}
