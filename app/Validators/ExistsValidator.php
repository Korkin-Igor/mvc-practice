<?php

namespace Validators;

use Illuminate\Database\Capsule\Manager as Capsule;
use Src\Validator\AbstractValidator;

class ExistsValidator extends AbstractValidator
{
    protected string $message = 'Поле :field содержит несуществующее значение';

    public function rule(): bool
    {
        if (count($this->args) < 2 || $this->value === null || $this->value === '') {
            return false;
        }

        return (bool) Capsule::table($this->args[0])
            ->where($this->args[1], $this->value)
            ->count();
    }
}
