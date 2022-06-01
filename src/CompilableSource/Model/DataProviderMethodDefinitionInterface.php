<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

interface DataProviderMethodDefinitionInterface extends MethodDefinitionInterface
{
    /**
     * @return array<mixed>
     */
    public function getData(): array;
}
