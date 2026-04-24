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
    echo '<pre>';
    print_r($exception);
    echo '</pre>';
}
