<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Validator;

class ValidResult extends AbstractResult
{
    public function __construct(mixed $subject)
    {
        parent::__construct(true, $subject);
    }
}
