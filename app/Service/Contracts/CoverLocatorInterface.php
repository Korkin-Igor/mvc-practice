<?php

namespace Service\Contracts;

interface CoverLocatorInterface
{
    public function find(int $bookId): ?string;
}
