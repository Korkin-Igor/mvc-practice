<?php

namespace app\Interfaces;

interface CoverLocatorInterface
{
    public function find(int $bookId): ?string;
}
