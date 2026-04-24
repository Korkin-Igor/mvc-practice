<?php

namespace Service;

use DateInterval;
use DateTimeImmutable;
use Model\Book;
use Model\BookCopy;
use Model\Booking;
use Model\User;
use Throwable;

class BookingService
{
    private const COPY_IN_ROOM = 1;
    private const COPY_BORROWED = 2;
    private const COPY_RESERVED = 3;
    private const COPY_DIGITAL = 4;

    private const BOOKING_PENDING = 1;
    private const BOOKING_APPROVED = 2;
    private const BOOKING_FINISHED = 3;
    private const BOOKING_OVERDUE = 4;
    private const BOOKING_CANCELLED = 5;

    public function reserveBook(User $user, int $bookId): OperationResult
    {
        try {
            $book = Book::find($bookId);
            if (!$book) {
                return OperationResult::failure('Книга не найдена.');
            }

            if ($this->hasOpenBookingForBook($user->id, $bookId)) {
                return OperationResult::failure('У вас уже есть активная бронь или заявка на эту книгу.');
            }

            $copy = BookCopy::where('book_id', $bookId)
                ->where('status_id', self::COPY_IN_ROOM)
                ->orderBy('id')
                ->first();

            if (!$copy) {
                return OperationResult::failure('Свободных экземпляров сейчас нет.');
            }

            Booking::create([
                'book_copy_id' => $copy->id,
                'reader_id' => $user->id,
                'approved_by' => null,
                'status_id' => self::BOOKING_PENDING,
                'due_date' => null,
                'created_at' => (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
            ]);

            $copy->status_id = self::COPY_RESERVED;
            $copy->save();

            return OperationResult::success('Заявка на бронирование отправлена библиотекарю.');
        } catch (Throwable $exception) {
            return OperationResult::failure('Не удалось оформить бронирование. Проверьте структуру таблиц и подключение к БД.');
        }
    }

    public function extendBooking(User $user, int $bookingId): OperationResult
    {
        try {
            $booking = Booking::with(['copy.storagePlace', 'status'])
                ->where('reader_id', $user->id)
                ->find($bookingId);

            if (!$booking || (int) $booking->status_id !== self::BOOKING_APPROVED) {
                return OperationResult::failure('Продлить можно только активную выдачу.');
            }

            if ($this->hasQueue($booking)) {
                return OperationResult::failure('Продление недоступно: на экземпляр уже есть очередь.');
            }

            $dueDate = $booking->due_date
                ? new DateTimeImmutable($booking->due_date)
                : new DateTimeImmutable('today');

            $booking->due_date = $dueDate->add(new DateInterval('P7D'))->format('Y-m-d');
            $booking->save();

            return OperationResult::success('Срок пользования книгой продлён ещё на 7 дней.');
        } catch (Throwable $exception) {
            return OperationResult::failure('Продление недоступно. Проверьте структуру таблиц и подключение к БД.');
        }
    }

    public function updateByLibrarian(User $user, int $bookingId, string $action): OperationResult
    {
        try {
            $booking = Booking::with(['copy.storagePlace', 'status'])->find($bookingId);
            if (!$booking) {
                return OperationResult::failure('Бронирование не найдено.');
            }

            if ($action === 'approve') {
                $message = $this->approveBooking($booking, $user);
            } elseif ($action === 'reject') {
                $message = $this->rejectBooking($booking, $user);
            } else {
                $message = $this->returnBook($booking, $user);
            }

            $booking->save();
            return OperationResult::success($message);
        } catch (Throwable $exception) {
            return OperationResult::failure('Операция недоступна. Проверьте структуру таблиц и подключение к БД.');
        }
    }

    public function getReaderBookings(User $user): array
    {
        try {
            $items = [];
            foreach (
                Booking::with(['copy.book', 'copy.status', 'status'])
                    ->where('reader_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get() as $booking
            ) {
                $items[] = $this->buildReaderBookingCard($booking);
            }

            return $this->buildReaderOverview($items);
        } catch (Throwable $exception) {
            return $this->buildReaderOverview([]);
        }
    }

    public function getLibrarianBookings(): array
    {
        try {
            $items = [];
            foreach (
                Booking::with(['copy.book', 'reader', 'status'])
                    ->orderBy('created_at', 'desc')
                    ->get() as $booking
            ) {
                $items[] = $this->buildLibrarianBookingCard($booking);
            }

            return $this->buildLibrarianOverview($items);
        } catch (Throwable $exception) {
            return $this->buildLibrarianOverview([]);
        }
    }

    public function getReaderOpenBookingBookIds(User $user): array
    {
        $ids = [];

        foreach (
            Booking::with('copy')
                ->where('reader_id', $user->id)
                ->whereIn('status_id', [
                    self::BOOKING_PENDING,
                    self::BOOKING_APPROVED,
                    self::BOOKING_OVERDUE,
                ])
                ->get() as $booking
        ) {
            $bookId = (int) ($booking->copy->book_id ?? 0);
            if ($bookId > 0) {
                $ids[$bookId] = true;
            }
        }

        return array_map('intval', array_keys($ids));
    }

    private function approveBooking(Booking $booking, User $user): string
    {
        $booking->status_id = self::BOOKING_APPROVED;
        $booking->approved_by = $user->id;

        if (!$booking->due_date) {
            $booking->due_date = (new DateTimeImmutable('today'))
                ->add(new DateInterval('P14D'))
                ->format('Y-m-d');
        }

        if ($booking->copy) {
            $booking->copy->status_id = self::COPY_BORROWED;
            $booking->copy->save();
        }

        return 'Бронирование подтверждено.';
    }

    private function rejectBooking(Booking $booking, User $user): string
    {
        $booking->status_id = self::BOOKING_CANCELLED;
        $booking->approved_by = $user->id;

        if ($booking->copy) {
            $booking->copy->status_id = $this->availableCopyStatusId($booking->copy);
            $booking->copy->save();
        }

        return 'Бронирование отклонено.';
    }

    private function returnBook(Booking $booking, User $user): string
    {
        $booking->status_id = self::BOOKING_FINISHED;
        $booking->approved_by = $user->id;

        if ($booking->copy) {
            $booking->copy->status_id = $this->hasPendingRequestsForCopy($booking->book_copy_id)
                ? self::COPY_RESERVED
                : $this->availableCopyStatusId($booking->copy);
            $booking->copy->save();
        }

        return 'Экземпляр возвращён в фонд.';
    }

    private function buildReaderBookingCard(Booking $booking): array
    {
        return $this->buildBaseBookingCard(
            $booking,
            (int) $booking->status_id === self::BOOKING_APPROVED && !$this->hasQueue($booking)
        );
    }

    private function buildLibrarianBookingCard(Booking $booking): array
    {
        $item = $this->buildBaseBookingCard($booking, false);
        $item['reader_name'] = $booking->reader->name ?? 'Читатель не указан';
        $item['can_approve'] = (int) $booking->status_id === self::BOOKING_PENDING;
        $item['can_reject'] = (int) $booking->status_id === self::BOOKING_PENDING;
        $item['can_return'] = in_array((int) $booking->status_id, [self::BOOKING_APPROVED, self::BOOKING_OVERDUE], true);

        return $item;
    }

    private function buildBaseBookingCard(Booking $booking, bool $canExtend): array
    {
        $deadline = $booking->due_date ? new DateTimeImmutable($booking->due_date) : null;
        $days = null;
        if ($deadline) {
            $days = (int) (new DateTimeImmutable('today'))->diff($deadline)->format('%r%a');
        }

        [$group, $hint] = $this->resolveBookingPresentation((int) $booking->status_id, $days);

        return [
            'id' => $booking->id,
            'name' => $booking->copy->book->name ?? 'Без названия',
            'author' => $booking->copy->book->author ?? 'Автор не указан',
            'status' => $booking->status->name ?? 'Неизвестно',
            'created_at' => $booking->created_at
                ? (new DateTimeImmutable($booking->created_at))->format('Y-m-d')
                : '—',
            'due_date' => $deadline ? $deadline->format('Y-m-d') : '—',
            'hint' => $hint,
            'group' => $group,
            'can_extend' => $canExtend,
        ];
    }

    private function resolveBookingPresentation(int $statusId, ?int $days): array
    {
        if ($statusId === self::BOOKING_PENDING) {
            return ['pending', 'Запрос ожидает обработки библиотекарем'];
        }

        if ($statusId === self::BOOKING_APPROVED) {
            if ($days !== null && $days < 0) {
                return ['overdue', 'Просрочена на ' . abs($days) . ' дн.'];
            }

            return ['active', 'Осталось ' . max(0, (int) $days) . ' дн.'];
        }

        if ($statusId === self::BOOKING_OVERDUE) {
            if ($days !== null) {
                return ['overdue', 'Просрочена на ' . abs($days) . ' дн.'];
            }

            return ['overdue', 'Срок пользования истёк'];
        }

        if ($statusId === self::BOOKING_FINISHED) {
            return ['completed', 'Экземпляр уже возвращён'];
        }

        if ($statusId === self::BOOKING_CANCELLED) {
            return ['cancelled', 'Запрос был отклонён'];
        }

        return ['completed', 'Архивная запись'];
    }

    private function buildReaderOverview(array $items): array
    {
        $groups = $this->groupBookings($items);

        return [
            'stats' => [
                'active' => count($groups['active']),
                'overdue' => count($groups['overdue']),
                'pending' => count($groups['pending']),
            ],
            'groups' => $groups,
        ];
    }

    private function buildLibrarianOverview(array $items): array
    {
        $groups = $this->groupBookings($items);

        return [
            'stats' => [
                'pending' => count($groups['pending']),
                'approved' => count($groups['active']),
                'completed' => count($groups['completed']),
            ],
            'groups' => $groups,
        ];
    }

    private function groupBookings(array $items): array
    {
        $groups = [
            'pending' => [],
            'overdue' => [],
            'active' => [],
            'completed' => [],
        ];

        foreach ($items as $item) {
            if (!array_key_exists($item['group'], $groups)) {
                continue;
            }

            $groups[$item['group']][] = $item;
        }

        return $groups;
    }

    private function hasQueue(Booking $booking): bool
    {
        return Booking::where('book_copy_id', $booking->book_copy_id)
            ->where('status_id', self::BOOKING_PENDING)
            ->where('id', '<>', $booking->id)
            ->exists();
    }

    private function hasOpenBookingForBook(int $readerId, int $bookId): bool
    {
        return Booking::where('reader_id', $readerId)
            ->whereIn('status_id', [
                self::BOOKING_PENDING,
                self::BOOKING_APPROVED,
                self::BOOKING_OVERDUE,
            ])
            ->whereHas('copy', function ($builder) use ($bookId) {
                $builder->where('book_id', $bookId);
            })
            ->exists();
    }

    private function hasPendingRequestsForCopy(int $copyId): bool
    {
        return Booking::where('book_copy_id', $copyId)
            ->where('status_id', self::BOOKING_PENDING)
            ->exists();
    }

    private function availableCopyStatusId(BookCopy $copy): int
    {
        $storagePlace = mb_strtolower((string) ($copy->storagePlace->name ?? ''), 'UTF-8');
        if (mb_strpos($storagePlace, 'электрон') !== false) {
            return self::COPY_DIGITAL;
        }

        return self::COPY_IN_ROOM;
    }
}
