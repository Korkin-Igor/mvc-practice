<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class UploadedValidator extends AbstractValidator
{
    protected string $message = 'Поле :field должно содержать загруженный файл';

    public function rule(): bool
    {
        return is_array($this->value)
            && ($this->value['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
            && !empty($this->value['tmp_name'] ?? '');
    }
}
