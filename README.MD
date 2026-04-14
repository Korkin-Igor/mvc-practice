# Проект web-base

Окружение использует или содержит следующие компоненты:
* [Docker](https://www.docker.com/) - контейнеризация приложений
* [Docker Compose](https://docs.docker.com/compose/) - управление контейнерами
* [Apache + PHP](https://hub.docker.com/layers/library/php/8-apache/images/sha256-1cf5e82a09672ce57b70d55ba400402099142ccbc762491129ed8ecc246c55f4?context=explore) - контейнер с веб-сервером Apache и php
* [Mariadb 10.7](https://hub.docker.com/r/168447636/mariadb) - контейнер с СУБД Mariadb 10.7
* [Phpmyadmin](https://hub.docker.com/r/phpmyadmin/phpmyadmin) - контейнер с Phpmyadmin

## 1. Установка
*Примечание: для работы окружения нужен установленный docker и docker compose*

Склонировать репозиторий

`git clone https://github.com/korkin-igor/mvc-practice`

## 2. Настройка

Скопировать файл `.env.example` в `.env` и заполнить его:

1. PROJECT_NAME - название проекта. По-умолчанию `project`;
2. USER - логин пользователя под которым будет запущен веб-сервер в  контейнере с php. Если пустой, то логин текущий логин ползователя или `user` (зависит от операционной системы). Также такой пользователь будет создан для доступа к базе данных в контейнере с mariadb;
3. PASSWORD - пароль пользователя внутри контейнера с php. По-умолчанию `password`. Также такой пароль будет установлен для доступа к базе данных в контейнере с mariadb;
4. WEB_PORT - порт для веб-сервера. По-умолчанию `10000`;
5. DB_NAME - имя базы данных в контейнере с mariadb. По-умолчанию `db`;
6. USER_DB_PASSWORD - пароль для суперпользователя (логин `root`) для СУБД в контейнере с mariadb. По-умолчанию `QWEasd123!`;
7. WEB_PORT_PHPMYADMIN - порт для phpmyadmin. По-умолчанию `8888`.

## 3. Запуск и остановка

Для запуска окружения необходимо в директории с `docker-compose.yml` выполнить командц `docker compose up -d`.

Для остановки окружения необходимо в директории с `docker-compose.yml` выполнить командц `docker compose down`.

