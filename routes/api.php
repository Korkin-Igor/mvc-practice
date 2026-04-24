<?php

use Src\Route;

Route::add('POST', '/auth/login', [Controller\ApiController::class, 'login']);
Route::add('POST', '/auth/logout', [Controller\ApiController::class, 'logout'])
    ->middleware('auth');

Route::add('GET', '/catalog', [Controller\ApiController::class, 'catalog']);

Route::add('GET', '/bookings/me', [Controller\ApiController::class, 'myBookings'])
    ->middleware('auth', 'role:reader');
Route::add('POST', '/books/{id:\d+}/reserve', [Controller\ApiController::class, 'reserveBook'])
    ->middleware('auth', 'role:reader');
Route::add('POST', '/bookings/{id:\d+}/extend', [Controller\ApiController::class, 'extendBooking'])
    ->middleware('auth', 'role:reader');

Route::add('GET', '/librarian/bookings', [Controller\ApiController::class, 'librarianBookings'])
    ->middleware('auth', 'role:librarian');
Route::add('POST', '/librarian/bookings/{id:\d+}/approve', [Controller\ApiController::class, 'approveBooking'])
    ->middleware('auth', 'role:librarian');
Route::add('POST', '/librarian/bookings/{id:\d+}/reject', [Controller\ApiController::class, 'rejectBooking'])
    ->middleware('auth', 'role:librarian');
Route::add('POST', '/librarian/bookings/{id:\d+}/return', [Controller\ApiController::class, 'returnBooking'])
    ->middleware('auth', 'role:librarian');
