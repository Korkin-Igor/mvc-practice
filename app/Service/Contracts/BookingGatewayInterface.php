<?php

namespace Service\Contracts;

use Model\Book;
use Model\BookCopy;
use Model\Booking;

interface BookingGatewayInterface
{
    public function findBook(int $bookId): ?Book;

    public function hasOpenBookingForBook(int $readerId, int $bookId): bool;

    public function findFirstAvailableCopy(int $bookId, int $statusId): ?BookCopy;

    public function createBooking(array $attributes): Booking;

    public function findReaderBooking(int $readerId, int $bookingId): ?Booking;

    public function findBooking(int $bookingId): ?Booking;

    public function getReaderBookings(int $readerId): array;

    public function getLibrarianBookings(): array;

    public function getReaderOpenBookings(int $readerId, array $statusIds): array;

    public function hasQueue(Booking $booking): bool;

    public function hasPendingRequestsForCopy(int $copyId): bool;
}
