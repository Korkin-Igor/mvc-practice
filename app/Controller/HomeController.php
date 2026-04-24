<?php

namespace Controller;

use Src\Request;

class HomeController extends BaseController
{
    public function index(Request $request): string
    {
        $redirect = $this->redirectIfAuthenticated();
        if ($redirect !== null) {
            return $redirect;
        }

        return $this->renderPage('site.home', [
            'pageTitle' => 'Либрари',
            'pageClass' => 'page-auth page-home',
            'preferredRole' => $this->input($request, 'role'),
        ]);
    }
}
