<?php

namespace Controller;

use Service\BookingService;
use Service\BookService;
use Service\CatalogService;
use Src\Request;
use Src\Validator\Validator;

class LibrarianController extends BaseController
{
    private CatalogService $catalogService;
    private BookingService $bookingService;
    private BookService $bookService;

    public function __construct()
    {
        $this->catalogService = new CatalogService();
        $this->bookingService = new BookingService();
        $this->bookService = new BookService();
    }

    public function storage(Request $request): string
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return '';
        }

        $search = $this->input($request, 'q');
        $status = $this->input($request, 'status');

        return $this->renderPage('librarian.storage', [
            'pageTitle' => 'Хранилище книг',
            'pageClass' => 'page-app',
            'activeNav' => 'storage',
            'storageSearch' => $search,
            'storageStatus' => $status,
            'storagePlaces' => $this->catalogService->getStoragePlaces(),
            'storageStatuses' => $this->catalogService->getStorageStatuses(),
            'storageRows' => $this->catalogService->getStorageRows($search, $status),
        ]);
    }

    public function createBook(Request $request): string
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return '';
        }

        $validator = $this->createBookValidator($request);
        if ($validator->fails()) {
            $this->flash('error', $this->validationMessage($validator));
            return $this->redirect('/storage');
        }

        $result = $this->bookService->createBookWithAssets($request->all());
        $this->flash($result->isSuccess() ? 'success' : 'error', $result->getMessage());

        return $this->redirect('/storage');
    }

    public function bookings(Request $request): string
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return '';
        }

        $bookings = $this->bookingService->getLibrarianBookings();

        return $this->renderPage('librarian.bookings', [
            'pageTitle' => 'Брони',
            'pageClass' => 'page-app',
            'activeNav' => 'bookings',
            'bookingStats' => $bookings['stats'],
            'bookingGroups' => $bookings['groups'],
        ]);
    }

    public function approveBooking(int $id, Request $request): string
    {
        return $this->updateBooking($id, 'approve');
    }

    public function rejectBooking(int $id, Request $request): string
    {
        return $this->updateBooking($id, 'reject');
    }

    public function returnBooking(int $id, Request $request): string
    {
        return $this->updateBooking($id, 'return');
    }

    private function updateBooking(int $id, string $action): string
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return '';
        }

        $result = $this->bookingService->updateByLibrarian($user, $id, $action);
        $this->flash($result->isSuccess() ? 'success' : 'error', $result->getMessage());

        return $this->redirect('/bookings');
    }

    private function createBookValidator(Request $request): Validator
    {
        return $this->validate($request->all(), [
            'name' => ['required'],
            'author' => ['required'],
            'inventory_number' => ['required', 'unique:books_copies,inventory_number'],
            'storage_place_id' => ['required', 'exists:storage_places,id'],
            'cover_image' => ['uploaded', 'image', 'maxSize:2048'],
            'digital_file' => ['uploaded', 'extension:pdf,txt,epub', 'maxSize:10240'],
        ], [
            'required' => 'Поле :field пусто',
            'unique' => 'Поле :field должно быть уникально',
            'exists' => 'Поле :field содержит неверное значение',
            'uploaded' => 'Поле :field должно содержать загруженный файл',
            'image' => 'Поле :field должно содержать изображение JPG, PNG, GIF или WEBP',
            'extension' => 'Поле :field должно содержать файл формата PDF, TXT или EPUB',
            'maxSize' => 'Поле :field превышает допустимый размер файла',
        ]);
    }
}
