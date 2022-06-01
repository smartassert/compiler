<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodArguments;

use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\HasMetadataInterface;
use webignition\StubbleResolvable\ResolvableInterface;

interface MethodArgumentsInterface extends HasMetadataInterface, ResolvableInterface
{
    /**
     * @return ExpressionInterface[]
     */
    public function getArguments(): array;

    public function getFormat(): string;
}
