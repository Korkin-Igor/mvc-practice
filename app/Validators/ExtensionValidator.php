<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class ExtensionValidator extends AbstractValidator
{
    protected string $message = 'Поле :field содержит файл недопустимого типа';

    public function rule(): bool
    {
        if (!is_array($this->value) || ($this->value['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $name = (string) ($this->value['name'] ?? '');
        $extension = mb_strtolower(pathinfo($name, PATHINFO_EXTENSION), 'UTF-8');
        $allowed = array_map(static function ($item) {
            return mb_strtolower((string) $item, 'UTF-8');
        }, $this->args);

        return $extension !== '' && in_array($extension, $allowed, true);
    }
}
