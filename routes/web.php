<?php

use Src\Route;

Route::add('GET', '/', [Controller\HomeController::class, 'index']);
Route::add('GET', '/go', [Controller\HomeController::class, 'index']);
Route::add('GET', '/hello', [Controller\HomeController::class, 'index']);

Route::add(['GET', 'POST'], '/signup', [Controller\AuthController::class, 'signup']);
Route::add(['GET', 'POST'], '/login', [Controller\AuthController::class, 'login']);
Route::add('GET', '/logout', [Controller\AuthController::class, 'logout'])
    ->middleware('auth');

Route::add('GET', '/catalog', [Controller\ReaderController::class, 'catalog'])
    ->middleware('auth', 'role:reader');
Route::add('POST', '/catalog/{id:\d+}/reserve', [Controller\ReaderController::class, 'reserveBook'])
    ->middleware('auth', 'role:reader');
Route::add('GET', '/my-bookings', [Controller\ReaderController::class, 'bookings'])
    ->middleware('auth', 'role:reader');
Route::add('POST', '/my-bookings/{id:\d+}/extend', [Controller\ReaderController::class, 'extendBooking'])
    ->middleware('auth', 'role:reader');

Route::add('GET', '/storage', [Controller\LibrarianController::class, 'storage'])
    ->middleware('auth', 'role:librarian');
Route::add('GET', '/bookings', [Controller\LibrarianController::class, 'bookings'])
    ->middleware('auth', 'role:librarian');
Route::add('POST', '/bookings/{id:\d+}/approve', [Controller\LibrarianController::class, 'approveBooking'])
    ->middleware('auth', 'role:librarian');
Route::add('POST', '/bookings/{id:\d+}/reject', [Controller\LibrarianController::class, 'rejectBooking'])
    ->middleware('auth', 'role:librarian');
Route::add('POST', '/bookings/{id:\d+}/return', [Controller\LibrarianController::class, 'returnBooking'])
    ->middleware('auth', 'role:librarian');
