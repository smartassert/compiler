<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

interface AssignmentExpressionInterface extends ExpressionInterface
{
    public function getVariable(): ExpressionInterface;

    public function getValue(): ExpressionInterface;

    public function getOperator(): string;
}
