<?php

namespace Service\Gateways;

use Model\Book;
use Model\BookCopy;
use Model\Booking;
use Service\Contracts\BookingGatewayInterface;

class EloquentBookingGateway implements BookingGatewayInterface
{
    public function findBook(int $bookId): ?Book
    {
        return Book::find($bookId);
    }

    public function hasOpenBookingForBook(int $readerId, int $bookId): bool
    {
        return Booking::where('reader_id', $readerId)
            ->whereIn('status_id', [1, 2, 4])
            ->whereHas('copy', function ($builder) use ($bookId) {
                $builder->where('book_id', $bookId);
            })
            ->exists();
    }

    public function findFirstAvailableCopy(int $bookId, int $statusId): ?BookCopy
    {
        return BookCopy::where('book_id', $bookId)
            ->where('status_id', $statusId)
            ->orderBy('id')
            ->first();
    }

    public function createBooking(array $attributes): Booking
    {
        return Booking::create($attributes);
    }

    public function findReaderBooking(int $readerId, int $bookingId): ?Booking
    {
        return Booking::with(['copy.storagePlace', 'status'])
            ->where('reader_id', $readerId)
            ->find($bookingId);
    }

    public function findBooking(int $bookingId): ?Booking
    {
        return Booking::with(['copy.storagePlace', 'status'])->find($bookingId);
    }

    public function getReaderBookings(int $readerId): array
    {
        return Booking::with(['copy.book', 'copy.status', 'status'])
            ->where('reader_id', $readerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    public function getLibrarianBookings(): array
    {
        return Booking::with(['copy.book', 'reader', 'status'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    public function getReaderOpenBookings(int $readerId, array $statusIds): array
    {
        return Booking::with('copy')
            ->where('reader_id', $readerId)
            ->whereIn('status_id', $statusIds)
            ->get()
            ->all();
    }

    public function hasQueue(Booking $booking): bool
    {
        return Booking::where('book_copy_id', $booking->book_copy_id)
            ->where('status_id', 1)
            ->where('id', '<>', $booking->id)
            ->exists();
    }

    public function hasPendingRequestsForCopy(int $copyId): bool
    {
        return Booking::where('book_copy_id', $copyId)
            ->where('status_id', 1)
            ->exists();
    }
}
