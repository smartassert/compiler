<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Validator;

interface ResultInterface
{
    public function getIsValid(): bool;

    public function getSubject(): mixed;
}
