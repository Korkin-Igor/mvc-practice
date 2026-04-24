<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateInterval;
use DateTimeImmutable;
use Service\BookingService;
use Service\Contracts\BookingGatewayInterface;
use Tests\TestCase;

final class BookingServiceTest extends TestCase
{
    public function testReserveBookCreatesPendingBookingAndReservesCopy(): void
    {
        $reader = $this->makeUser(['id' => 10]);
        $book = $this->makeBook(['id' => 50]);
        $copy = $this->makeCopy([
            'id' => 7,
            'book_id' => 50,
            'status_id' => 1,
        ], $book, $this->makeStoragePlace());

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->expects($this->once())->method('findBook')->with(50)->willReturn($book);
        $gateway->expects($this->once())->method('hasOpenBookingForBook')->with(10, 50)->willReturn(false);
        $gateway->expects($this->once())->method('findFirstAvailableCopy')->with(50, 1)->willReturn($copy);
        $gateway->expects($this->once())
            ->method('createBooking')
            ->with($this->callback(function (array $payload) use ($reader, $copy): bool {
                return $payload['book_copy_id'] === $copy->id
                    && $payload['reader_id'] === $reader->id
                    && $payload['status_id'] === 1
                    && $payload['approved_by'] === null;
            }));

        $service = new BookingService($gateway);
        $result = $service->reserveBook($reader, 50);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('Заявка на бронирование отправлена библиотекарю.', $result->getMessage());
        $this->assertSame(3, (int) $copy->status_id);
        $this->assertSame(1, $copy->saveCalls);
    }

    public function testReserveBookRejectsDuplicateOpenBooking(): void
    {
        $reader = $this->makeUser(['id' => 10]);
        $book = $this->makeBook(['id' => 50]);

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->method('findBook')->willReturn($book);
        $gateway->expects($this->once())->method('hasOpenBookingForBook')->with(10, 50)->willReturn(true);
        $gateway->expects($this->never())->method('findFirstAvailableCopy');
        $gateway->expects($this->never())->method('createBooking');

        $service = new BookingService($gateway);
        $result = $service->reserveBook($reader, 50);

        $this->assertFalse($result->isSuccess());
        $this->assertSame('У вас уже есть активная бронь или заявка на эту книгу.', $result->getMessage());
    }

    public function testExtendBookingAddsSevenDaysWithoutQueue(): void
    {
        $reader = $this->makeUser(['id' => 10]);
        $copy = $this->makeCopy([
            'id' => 7,
            'status_id' => 2,
        ], $this->makeBook(), $this->makeStoragePlace());
        $booking = $this->makeBooking([
            'id' => 99,
            'reader_id' => 10,
            'status_id' => 2,
            'due_date' => '2026-05-01',
        ], $copy, $reader, $this->makeBookingStatus([
            'id' => 2,
            'name' => 'Подтверждена',
        ]));

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->expects($this->once())->method('findReaderBooking')->with(10, 99)->willReturn($booking);
        $gateway->expects($this->once())->method('hasQueue')->with($booking)->willReturn(false);

        $service = new BookingService($gateway);
        $result = $service->extendBooking($reader, 99);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('2026-05-08', $booking->due_date);
        $this->assertSame(1, $booking->saveCalls);
    }

    public function testExtendBookingRejectsQueue(): void
    {
        $reader = $this->makeUser(['id' => 10]);
        $booking = $this->makeBooking([
            'id' => 99,
            'reader_id' => 10,
            'status_id' => 2,
            'due_date' => '2026-05-01',
        ], $this->makeCopy(), $reader, $this->makeBookingStatus([
            'id' => 2,
            'name' => 'Подтверждена',
        ]));

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->method('findReaderBooking')->willReturn($booking);
        $gateway->expects($this->once())->method('hasQueue')->with($booking)->willReturn(true);

        $service = new BookingService($gateway);
        $result = $service->extendBooking($reader, 99);

        $this->assertFalse($result->isSuccess());
        $this->assertSame('Продление недоступно: на экземпляр уже есть очередь.', $result->getMessage());
        $this->assertSame('2026-05-01', $booking->due_date);
        $this->assertSame(0, $booking->saveCalls);
    }

    public function testApproveBookingSetsDueDateApproverAndBorrowedStatus(): void
    {
        $reader = $this->makeUser(['id' => 10]);
        $librarian = $this->makeUser([
            'id' => 20,
            'role_id' => 1,
        ]);
        $copy = $this->makeCopy([
            'id' => 7,
            'status_id' => 3,
        ], $this->makeBook(), $this->makeStoragePlace());
        $booking = $this->makeBooking([
            'id' => 99,
            'reader_id' => 10,
            'status_id' => 1,
            'due_date' => null,
        ], $copy, $reader, $this->makeBookingStatus([
            'id' => 1,
            'name' => 'Ожидает',
        ]));

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->expects($this->once())->method('findBooking')->with(99)->willReturn($booking);

        $service = new BookingService($gateway);
        $result = $service->updateByLibrarian($librarian, 99, 'approve');

        $this->assertTrue($result->isSuccess());
        $this->assertSame(2, (int) $booking->status_id);
        $this->assertSame($librarian->id, (int) $booking->approved_by);
        $this->assertSame(
            (new DateTimeImmutable('today'))->add(new DateInterval('P14D'))->format('Y-m-d'),
            $booking->due_date
        );
        $this->assertSame(2, (int) $copy->status_id);
        $this->assertSame(1, $copy->saveCalls);
        $this->assertSame(1, $booking->saveCalls);
    }

    public function testReturnBookKeepsReservedStatusWhenQueueExists(): void
    {
        $librarian = $this->makeUser([
            'id' => 20,
            'role_id' => 1,
        ]);
        $copy = $this->makeCopy([
            'id' => 7,
            'status_id' => 2,
        ], $this->makeBook(), $this->makeStoragePlace());
        $booking = $this->makeBooking([
            'id' => 99,
            'status_id' => 2,
            'book_copy_id' => 7,
        ], $copy, $this->makeUser(), $this->makeBookingStatus([
            'id' => 2,
            'name' => 'Подтверждена',
        ]));

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->expects($this->once())->method('findBooking')->with(99)->willReturn($booking);
        $gateway->expects($this->once())->method('hasPendingRequestsForCopy')->with(7)->willReturn(true);

        $service = new BookingService($gateway);
        $result = $service->updateByLibrarian($librarian, 99, 'return');

        $this->assertTrue($result->isSuccess());
        $this->assertSame(3, (int) $booking->status_id);
        $this->assertSame(3, (int) $copy->status_id);
    }

    public function testReturnBookRestoresDigitalStatusForElectronicStorage(): void
    {
        $librarian = $this->makeUser([
            'id' => 20,
            'role_id' => 1,
        ]);
        $copy = $this->makeCopy([
            'id' => 7,
            'status_id' => 2,
        ], $this->makeBook(), $this->makeStoragePlace([
            'id' => 2,
            'name' => 'Электронный архив',
        ]));
        $booking = $this->makeBooking([
            'id' => 99,
            'status_id' => 2,
            'book_copy_id' => 7,
        ], $copy, $this->makeUser(), $this->makeBookingStatus([
            'id' => 2,
            'name' => 'Подтверждена',
        ]));

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->method('findBooking')->willReturn($booking);
        $gateway->expects($this->once())->method('hasPendingRequestsForCopy')->with(7)->willReturn(false);

        $service = new BookingService($gateway);
        $result = $service->updateByLibrarian($librarian, 99, 'return');

        $this->assertTrue($result->isSuccess());
        $this->assertSame(4, (int) $copy->status_id);
    }

    public function testGetReaderOpenBookingBookIdsReturnsUniqueIds(): void
    {
        $reader = $this->makeUser(['id' => 10]);
        $book = $this->makeBook(['id' => 5]);

        $firstBooking = $this->makeBooking([
            'book_copy_id' => 10,
            'status_id' => 1,
        ], $this->makeCopy([
            'id' => 10,
            'book_id' => 5,
        ], $book, $this->makeStoragePlace()));

        $secondBooking = $this->makeBooking([
            'book_copy_id' => 11,
            'status_id' => 2,
        ], $this->makeCopy([
            'id' => 11,
            'book_id' => 5,
        ], $book, $this->makeStoragePlace()));

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->expects($this->once())
            ->method('getReaderOpenBookings')
            ->with(10, [1, 2, 4])
            ->willReturn([$firstBooking, $secondBooking]);

        $service = new BookingService($gateway);

        $this->assertSame([5], $service->getReaderOpenBookingBookIds($reader));
    }

    public function testGetReaderBookingsBuildsGroupedOverview(): void
    {
        $reader = $this->makeUser(['id' => 10]);
        $book = $this->makeBook(['name' => 'DDD', 'author' => 'Evans']);

        $overdueBooking = $this->makeBooking([
            'id' => 1,
            'status_id' => 2,
            'due_date' => '2026-04-20',
        ], $this->makeCopy([], $book, $this->makeStoragePlace()), $reader, $this->makeBookingStatus([
            'id' => 2,
            'name' => 'Подтверждена',
        ]));
        $activeBooking = $this->makeBooking([
            'id' => 2,
            'status_id' => 2,
            'due_date' => '2026-04-30',
        ], $this->makeCopy([], $book, $this->makeStoragePlace()), $reader, $this->makeBookingStatus([
            'id' => 2,
            'name' => 'Подтверждена',
        ]));
        $pendingBooking = $this->makeBooking([
            'id' => 3,
            'status_id' => 1,
        ], $this->makeCopy([], $book, $this->makeStoragePlace()), $reader, $this->makeBookingStatus([
            'id' => 1,
            'name' => 'Ожидает',
        ]));

        $gateway = $this->createMock(BookingGatewayInterface::class);
        $gateway->expects($this->once())
            ->method('getReaderBookings')
            ->with(10)
            ->willReturn([$overdueBooking, $activeBooking, $pendingBooking]);
        $gateway->method('hasQueue')->willReturn(false);

        $service = new BookingService($gateway);
        $overview = $service->getReaderBookings($reader);

        $this->assertSame(['active' => 1, 'overdue' => 1, 'pending' => 1], $overview['stats']);
        $this->assertCount(1, $overview['groups']['active']);
        $this->assertCount(1, $overview['groups']['overdue']);
        $this->assertCount(1, $overview['groups']['pending']);
    }
}
