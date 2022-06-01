<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Construct;

class ReturnConstruct
{
    public function __toString(): string
    {
        return 'return';
    }
}
