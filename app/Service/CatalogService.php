<?php

namespace Service;

use app\Interfaces\CatalogGatewayInterface;
use app\Interfaces\CoverLocatorInterface;
use app\Repositories\EloquentCatalogGateway;
use app\Repositories\GlobCoverLocator;
use Model\Book;
use Model\BookCopy;
use Model\StoragePlace;
use Throwable;
use function Collect\collection;

class CatalogService
{
    private const COPY_IN_ROOM = 1;

    private CatalogGatewayInterface $gateway;
    private CoverLocatorInterface $coverLocator;

    public function __construct(
        ?CatalogGatewayInterface $gateway = null,
        ?CoverLocatorInterface $coverLocator = null
    ) {
        $this->gateway = $gateway ?? new EloquentCatalogGateway();
        $this->coverLocator = $coverLocator ?? new GlobCoverLocator(dirname(__DIR__, 2));
    }

    public function getCatalogBooks(string $search, array $openBookingBookIds): array
    {
        try {
            return collection($this->gateway->getCatalogBooks($search))
                ->map(function (Book $book) use ($openBookingBookIds): array {
                    $availableCopies = 0;

                    collection($book->copies->all())
                        ->each(function ($copy) use (&$availableCopies): void {
                            if ((int) $copy->status_id === self::COPY_IN_ROOM) {
                                $availableCopies++;
                            }
                        });

                    $hasOpenBooking = in_array((int) $book->id, $openBookingBookIds, true);

                    return [
                        'id' => $book->id,
                        'name' => $book->name,
                        'author' => $book->author ?: 'Автор не указан',
                        'description' => $book->description ?: 'Краткое описание пока не заполнено.',
                        'link' => $book->link,
                        'cover_url' => $this->coverLocator->find((int) $book->id),
                        'available_copies' => $availableCopies,
                        'can_reserve' => $availableCopies > 0 && !$hasOpenBooking,
                        'reserve_label' => $hasOpenBooking
                            ? 'Уже в брони'
                            : ($availableCopies > 0 ? 'Забронировать' : 'Нет экземпляров'),
                    ];
                })
                ->toArray();
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function getStorageStatuses(): array
    {
        try {
            return collection($this->gateway->getStorageStatuses())->toArray();
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function getStorageRows(string $search, string $status): array
    {
        try {
            return collection($this->gateway->getStorageCopies($search, $status))
                ->map(static function (BookCopy $copy): array {
                    return [
                    'name' => $copy->book->name ?? 'Без названия',
                    'author' => $copy->book->author ?? 'Автор не указан',
                    'inventory_number' => $copy->inventory_number,
                    'storage_place' => $copy->storagePlace->name ?? 'Не указано',
                    'status' => $copy->status->name ?? 'Неизвестно',
                    'barcode' => $copy->barcode ?? '—',
                    'qr_code' => $copy->qr_code ?? '—',
                    ];
                })
                ->toArray();
        } catch (Throwable $exception) {
            return [];
        }
    }

    public function getStoragePlaces(): array
    {
        try {
            return collection($this->gateway->getStoragePlaces())
                ->map(static function (StoragePlace $place): array {
                    return [
                        'id' => $place->id,
                        'name' => $place->name,
                    ];
                })
                ->toArray();
        } catch (Throwable $exception) {
            return [];
        }
    }
}
