<?php
//Включаем запрет на неявное преобразование типов
declare(strict_types=1);

// Скрываем deprecation-сообщения из устаревших зависимостей, не отключая остальные ошибки.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

//Включаем сессии на все страницы
session_start();

try {
    //Создаем экземпляр приложения и запускаем его
    $app = require_once __DIR__ . '/../core/bootstrap.php';
    $app->run();
} catch (\Throwable $exception) {
    $uri = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    if (strpos($uri, '/api') === 0) {
        $code = 500;
        $message = 'Внутренняя ошибка сервера.';

        if ($exception->getMessage() === 'NOT_FOUND') {
            $code = 404;
            $message = 'Маршрут не найден.';
        } elseif ($exception->getMessage() === 'METHOD_NOT_ALLOWED') {
            $code = 405;
            $message = 'HTTP-метод не поддерживается для этого маршрута.';
        }

        (new \Src\View())->toJSON([
            'message' => $message,
        ], $code);
    }

    echo '<pre>';
    print_r($exception);
    echo '</pre>';
}
