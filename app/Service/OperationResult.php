<?php

namespace Service;

class OperationResult
{
    private bool $success;
    private string $message;

    private function __construct(bool $success, string $message)
    {
        $this->success = $success;
        $this->message = $message;
    }

    public static function success(string $message = ''): self
    {
        return new self(true, $message);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
