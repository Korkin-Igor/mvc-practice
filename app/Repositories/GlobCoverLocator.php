<?php

namespace app\Repositories;

use app\Interfaces\CoverLocatorInterface;

class GlobCoverLocator implements CoverLocatorInterface
{
    private string $projectRoot;

    public function __construct(string $projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    public function find(int $bookId): ?string
    {
        $matches = glob($this->projectRoot . '/public/uploads/covers/book-' . $bookId . '.*');
        if (!$matches) {
            return null;
        }

        return '/public/uploads/covers/' . basename($matches[0]);
    }
}
