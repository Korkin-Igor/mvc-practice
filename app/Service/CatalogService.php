<?php

namespace Service;

use Model\Book;
use Model\BookCopy;
use Model\BookCopyStatus;
use Model\StoragePlace;
use Throwable;

class CatalogService
{
    private const COPY_IN_ROOM = 1;

    public function getCatalogBooks(string $search, array $openBookingBookIds): array
    {
        try {
            $query = Book::with('copies')->orderBy('name');

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', '%' . $search . '%')
                        ->orWhere('author', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            $books = [];
            foreach ($query->get() as $book) {
                $availableCopies = 0;
                foreach ($book->copies as $copy) {
                    if ((int) $copy->status_id === self::COPY_IN_ROOM) {
                        $availableCopies++;
                    }
                }

                $hasOpenBooking = in_array((int) $book->id, $openBookingBookIds, true);
                $books[] = [
                    'id' => $book->id,
                    'name' => $book->name,
                    'author' => $book->author ?: 'Автор не указан',
                    'description' => $book->description ?: 'Краткое описание пока не заполнено.',
                    'link' => $book->link,
                    'cover_url' => $this->coverUrl((int) $book->id),
                    'available_copies' => $availableCopies,
                    'can_reserve' => $availableCopies > 0 && !$hasOpenBooking,
                    'reserve_label' => $hasOpenBooking
                        ? 'Уже в брони'
                        : ($availableCopies > 0 ? 'Забронировать' : 'Нет экземпляров'),
                ];
            }

            return $books;
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function getStorageStatuses(): array
    {
        try {
            return BookCopyStatus::query()
                ->orderBy('id')
                ->pluck('name')
                ->all();
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function getStorageRows(string $search, string $status): array
    {
        try {
            $query = BookCopy::with(['book', 'status', 'storagePlace'])->orderBy('id');

            if ($status !== '') {
                $query->whereHas('status', function ($builder) use ($status) {
                    $builder->where('name', $status);
                });
            }

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder->where('inventory_number', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%')
                        ->orWhere('qr_code', 'like', '%' . $search . '%')
                        ->orWhereHas('book', function ($relation) use ($search) {
                            $relation->where('name', 'like', '%' . $search . '%')
                                ->orWhere('author', 'like', '%' . $search . '%');
                        });
                });
            }

            $rows = [];
            foreach ($query->get() as $copy) {
                $rows[] = [
                    'name' => $copy->book->name ?? 'Без названия',
                    'author' => $copy->book->author ?? 'Автор не указан',
                    'inventory_number' => $copy->inventory_number,
                    'storage_place' => $copy->storagePlace->name ?? 'Не указано',
                    'status' => $copy->status->name ?? 'Неизвестно',
                    'barcode' => $copy->barcode ?? '—',
                    'qr_code' => $copy->qr_code ?? '—',
                ];
            }

            return $rows;
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function getStoragePlaces(): array
    {
        try {
            $items = [];
            foreach (StoragePlace::query()->orderBy('name')->get() as $place) {
                $items[] = [
                    'id' => $place->id,
                    'name' => $place->name,
                ];
            }

            return $items;
        } catch (Throwable $exception) {
            return [];
        }
    }

    private function coverUrl(int $bookId): ?string
    {
        $matches = glob($this->projectRoot() . '/public/uploads/covers/book-' . $bookId . '.*');
        if (!$matches) {
            return null;
        }

        return '/public/uploads/covers/' . basename($matches[0]);
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}
