<?php

declare(strict_types=1);

namespace Tests;

use Model\Book;
use Model\BookCopy;
use Model\BookCopyStatus;
use Model\Booking;
use Model\BookingStatus;
use Model\StoragePlace;
use Model\User;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected function makeUser(array $attributes = []): User
    {
        $user = new User();
        $user->forceFill(array_merge([
            'id' => 1,
            'name' => 'Пользователь',
            'login' => 'user',
            'password' => 'secret',
            'role_id' => User::ROLE_READER,
        ], $attributes));

        return $user;
    }

    protected function makeBook(array $attributes = []): Book
    {
        $book = new Book();
        $book->forceFill(array_merge([
            'id' => 1,
            'name' => 'Книга',
            'author' => 'Автор',
            'description' => 'Описание',
            'link' => '/book.pdf',
        ], $attributes));

        return $book;
    }

    protected function makeCopy(array $attributes = [], ?Book $book = null, ?StoragePlace $storagePlace = null): BookCopy
    {
        $copy = new class extends BookCopy {
            public int $saveCalls = 0;

            public function save(array $options = []): bool
            {
                $this->saveCalls++;

                return true;
            }
        };

        $copy->forceFill(array_merge([
            'id' => 1,
            'inventory_number' => 'INV-1',
            'storage_place_id' => 1,
            'status_id' => 1,
            'book_id' => 1,
            'barcode' => 'BAR-1',
            'qr_code' => 'QR-1',
        ], $attributes));

        if ($book) {
            $copy->setRelation('book', $book);
        }

        if ($storagePlace) {
            $copy->setRelation('storagePlace', $storagePlace);
        }

        $copy->setRelation('status', $this->makeBookCopyStatus([
            'id' => (int) $copy->status_id,
            'name' => 'Статус экземпляра',
        ]));

        return $copy;
    }

    protected function makeBooking(
        array $attributes = [],
        ?BookCopy $copy = null,
        ?User $reader = null,
        ?BookingStatus $status = null
    ): Booking {
        $booking = new class extends Booking {
            public int $saveCalls = 0;

            public function save(array $options = []): bool
            {
                $this->saveCalls++;

                return true;
            }
        };

        $booking->forceFill(array_merge([
            'id' => 1,
            'book_copy_id' => 1,
            'reader_id' => 1,
            'approved_by' => null,
            'status_id' => 1,
            'due_date' => null,
            'created_at' => '2026-04-24 10:00:00',
        ], $attributes));

        if ($copy) {
            $booking->setRelation('copy', $copy);
        }

        if ($reader) {
            $booking->setRelation('reader', $reader);
        }

        $booking->setRelation('status', $status ?? $this->makeBookingStatus([
            'id' => (int) $booking->status_id,
            'name' => 'Статус брони',
        ]));

        return $booking;
    }

    protected function makeStoragePlace(array $attributes = []): StoragePlace
    {
        $place = new StoragePlace();
        $place->forceFill(array_merge([
            'id' => 1,
            'name' => 'Абонемент',
        ], $attributes));

        return $place;
    }

    protected function makeBookingStatus(array $attributes = []): BookingStatus
    {
        $status = new BookingStatus();
        $status->forceFill(array_merge([
            'id' => 1,
            'name' => 'Ожидает',
        ], $attributes));

        return $status;
    }

    protected function makeBookCopyStatus(array $attributes = []): BookCopyStatus
    {
        $status = new BookCopyStatus();
        $status->forceFill(array_merge([
            'id' => 1,
            'name' => 'В фонде',
        ], $attributes));

        return $status;
    }
}
