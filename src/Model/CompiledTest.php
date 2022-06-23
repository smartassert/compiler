<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Model;

class CompiledTest
{
    /**
     * @param non-empty-string $className
     */
    public function __construct(
        private readonly string $code,
        private readonly string $className
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return non-empty-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
