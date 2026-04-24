<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class ImageValidator extends AbstractValidator
{
    protected string $message = 'Поле :field должно быть изображением';

    public function rule(): bool
    {
        if (!is_array($this->value) || ($this->value['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $tmpName = $this->value['tmp_name'] ?? '';
        if ($tmpName === '' || !is_file($tmpName)) {
            return false;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpName);

        return in_array($mime, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ], true);
    }
}
