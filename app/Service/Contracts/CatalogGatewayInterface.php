<?php

namespace Service\Contracts;

interface CatalogGatewayInterface
{
    public function getCatalogBooks(string $search): array;

    public function getStorageStatuses(): array;

    public function getStorageCopies(string $search, string $status): array;

    public function getStoragePlaces(): array;
}
