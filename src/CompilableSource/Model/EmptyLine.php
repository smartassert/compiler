<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Body\BodyContentInterface;

class EmptyLine implements BodyContentInterface
{
    use ResolvableStringableTrait;

    public function __toString(): string
    {
        return '';
    }
}
