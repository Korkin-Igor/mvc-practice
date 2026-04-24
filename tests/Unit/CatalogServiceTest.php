<?php

declare(strict_types=1);

namespace Tests\Unit;

use Service\CatalogService;
use Service\Contracts\CatalogGatewayInterface;
use Service\Contracts\CoverLocatorInterface;
use Tests\TestCase;

final class CatalogServiceTest extends TestCase
{
    public function testGetCatalogBooksBuildsAvailabilityFlagsAndCoverUrl(): void
    {
        $firstBook = $this->makeBook([
            'id' => 1,
            'name' => 'Alpha',
        ]);
        $firstBook->setRelation('copies', collect([
            $this->makeCopy(['status_id' => 1], $firstBook, $this->makeStoragePlace()),
            $this->makeCopy(['status_id' => 3], $firstBook, $this->makeStoragePlace()),
        ]));

        $secondBook = $this->makeBook([
            'id' => 2,
            'name' => 'Beta',
        ]);
        $secondBook->setRelation('copies', collect([
            $this->makeCopy(['status_id' => 2], $secondBook, $this->makeStoragePlace()),
        ]));

        $gateway = $this->createMock(CatalogGatewayInterface::class);
        $gateway->expects($this->once())->method('getCatalogBooks')->with('alpha')->willReturn([$firstBook, $secondBook]);

        $coverLocator = $this->createMock(CoverLocatorInterface::class);
        $coverLocator->expects($this->exactly(2))
            ->method('find')
            ->willReturnMap([
                [1, '/public/uploads/covers/book-1.jpg'],
                [2, null],
            ]);

        $service = new CatalogService($gateway, $coverLocator);
        $books = $service->getCatalogBooks('alpha', [2]);

        $this->assertCount(2, $books);
        $this->assertSame(1, $books[0]['available_copies']);
        $this->assertTrue($books[0]['can_reserve']);
        $this->assertSame('/public/uploads/covers/book-1.jpg', $books[0]['cover_url']);
        $this->assertSame('Забронировать', $books[0]['reserve_label']);

        $this->assertSame(0, $books[1]['available_copies']);
        $this->assertFalse($books[1]['can_reserve']);
        $this->assertSame('Уже в брони', $books[1]['reserve_label']);
    }

    public function testGetStorageStatusesReturnsGatewayValues(): void
    {
        $gateway = $this->createMock(CatalogGatewayInterface::class);
        $gateway->expects($this->once())
            ->method('getStorageStatuses')
            ->willReturn(['В фонде', 'Выдана', 'Забронирована']);

        $service = new CatalogService($gateway, $this->createStub(CoverLocatorInterface::class));

        $this->assertSame(['В фонде', 'Выдана', 'Забронирована'], $service->getStorageStatuses());
    }

    public function testGetStorageRowsMapsRelatedEntities(): void
    {
        $book = $this->makeBook([
            'name' => 'DDD',
            'author' => 'Evans',
        ]);
        $copy = $this->makeCopy([
            'inventory_number' => 'INV-DDD',
            'barcode' => 'BAR-DDD',
            'qr_code' => 'QR-DDD',
        ], $book, $this->makeStoragePlace([
            'name' => 'Читальный зал',
        ]));
        $copy->setRelation('status', $this->makeBookCopyStatus([
            'name' => 'В фонде',
        ]));

        $gateway = $this->createMock(CatalogGatewayInterface::class);
        $gateway->expects($this->once())
            ->method('getStorageCopies')
            ->with('DDD', 'В фонде')
            ->willReturn([$copy]);

        $service = new CatalogService($gateway, $this->createStub(CoverLocatorInterface::class));
        $rows = $service->getStorageRows('DDD', 'В фонде');

        $this->assertSame([[
            'name' => 'DDD',
            'author' => 'Evans',
            'inventory_number' => 'INV-DDD',
            'storage_place' => 'Читальный зал',
            'status' => 'В фонде',
            'barcode' => 'BAR-DDD',
            'qr_code' => 'QR-DDD',
        ]], $rows);
    }

    public function testGetStoragePlacesMapsIdAndName(): void
    {
        $gateway = $this->createMock(CatalogGatewayInterface::class);
        $gateway->expects($this->once())
            ->method('getStoragePlaces')
            ->willReturn([
                $this->makeStoragePlace(['id' => 1, 'name' => 'Абонемент']),
                $this->makeStoragePlace(['id' => 2, 'name' => 'Электронный архив']),
            ]);

        $service = new CatalogService($gateway, $this->createStub(CoverLocatorInterface::class));

        $this->assertSame([
            ['id' => 1, 'name' => 'Абонемент'],
            ['id' => 2, 'name' => 'Электронный архив'],
        ], $service->getStoragePlaces());
    }
}
