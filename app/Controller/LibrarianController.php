<?php

namespace Controller;

use Service\BookingService;
use Service\CatalogService;
use Src\Request;

class LibrarianController extends BaseController
{
    private CatalogService $catalogService;
    private BookingService $bookingService;

    public function __construct()
    {
        $this->catalogService = new CatalogService();
        $this->bookingService = new BookingService();
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
            'storageStatuses' => $this->catalogService->getStorageStatuses(),
            'storageRows' => $this->catalogService->getStorageRows($search, $status),
        ]);
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
}
