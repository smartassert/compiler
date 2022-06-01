<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodInvocation;

use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;

interface InvocableInterface extends ExpressionInterface
{
    public function getCall(): string;

    public function getArguments(): MethodArgumentsInterface;
}
