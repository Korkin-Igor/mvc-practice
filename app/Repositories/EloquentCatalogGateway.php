<?php

namespace app\Repositories;

use app\Interfaces\CatalogGatewayInterface;
use Model\Book;
use Model\BookCopy;
use Model\BookCopyStatus;
use Model\StoragePlace;

class EloquentCatalogGateway implements CatalogGatewayInterface
{
    public function getCatalogBooks(string $search): array
    {
        $query = Book::with('copies')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('author', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        return $query->get()->all();
    }

    public function getStorageStatuses(): array
    {
        return BookCopyStatus::query()
            ->orderBy('id')
            ->pluck('name')
            ->all();
    }

    public function getStorageCopies(string $search, string $status): array
    {
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

        return $query->get()->all();
    }

    public function getStoragePlaces(): array
    {
        return StoragePlace::query()->orderBy('name')->get()->all();
    }
}
