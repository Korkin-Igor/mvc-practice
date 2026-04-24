<?php

namespace Controller;

use Service\BookingService;
use Service\CatalogService;
use Src\Request;

class ReaderController extends BaseController
{
    private CatalogService $catalogService;
    private BookingService $bookingService;

    public function __construct()
    {
        $this->catalogService = new CatalogService();
        $this->bookingService = new BookingService();
    }

    public function catalog(Request $request): string
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return '';
        }

        $search = $this->input($request, 'q');

        return $this->renderPage('reader.catalog', [
            'pageTitle' => 'Каталог',
            'pageClass' => 'page-app',
            'activeNav' => 'catalog',
            'catalogSearch' => $search,
            'catalogBooks' => $this->catalogService->getCatalogBooks(
                $search,
                $this->bookingService->getReaderOpenBookingBookIds($user)
            ),
        ]);
    }

    public function reserveBook(int $id, Request $request): string
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return '';
        }

        $result = $this->bookingService->reserveBook($user, $id);
        $this->flash($result->isSuccess() ? 'success' : 'error', $result->getMessage());

        return $this->redirect('/catalog');
    }

    public function bookings(Request $request): string
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return '';
        }

        $bookings = $this->bookingService->getReaderBookings($user);

        return $this->renderPage('reader.my-bookings', [
            'pageTitle' => 'Мои брони',
            'pageClass' => 'page-app',
            'activeNav' => 'my-bookings',
            'bookingStats' => $bookings['stats'],
            'bookingGroups' => $bookings['groups'],
        ]);
    }

    public function extendBooking(int $id, Request $request): string
    {
        $user = $this->authenticatedUser();
        if (!$user) {
            return '';
        }

        $result = $this->bookingService->extendBooking($user, $id);
        $this->flash($result->isSuccess() ? 'success' : 'error', $result->getMessage());

        return $this->redirect('/my-bookings');
    }
}
