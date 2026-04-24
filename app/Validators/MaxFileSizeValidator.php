<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class MaxFileSizeValidator extends AbstractValidator
{
    protected string $message = 'Поле :field превышает допустимый размер файла';

    public function rule(): bool
    {
        if (!is_array($this->value) || ($this->value['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $limitKb = (int) ($this->args[0] ?? 0);
        if ($limitKb <= 0) {
            return false;
        }

        return (int) ($this->value['size'] ?? 0) <= $limitKb * 1024;
    }
}
