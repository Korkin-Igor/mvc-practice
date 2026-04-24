<?php
return [
    //Класс аутентификации
    'auth' => \Src\Auth\Auth::class,
    //Класс пользователя
    'identity' => \Model\User::class,
    //Классы для middleware
    'routeMiddleware' => [
        'auth' => \Middlewares\AuthMiddleware::class,
        'role' => \Middlewares\RoleMiddleware::class,
    ],
    'routeAppMiddleware' => [
        'csrf' => \Middlewares\CSRFMiddleware::class,
        'trim' => \Middlewares\TrimMiddleware::class,
        'specialChars' => \Middlewares\SpecialCharsMiddleware::class,
    ],
    'validators' => [
        'required' => \Validators\RequireValidator::class,
        'unique' => \Validators\UniqueValidator::class,
        'exists' => \Validators\ExistsValidator::class,
        'uploaded' => \Validators\UploadedValidator::class,
        'image' => \Validators\ImageValidator::class,
        'extension' => \Validators\ExtensionValidator::class,
        'maxSize' => \Validators\MaxFileSizeValidator::class,
    ],
];
