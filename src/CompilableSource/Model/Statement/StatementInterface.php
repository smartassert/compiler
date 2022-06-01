<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Statement;

use SmartAssert\Compiler\CompilableSource\Model\Body\BodyContentInterface;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;

interface StatementInterface extends BodyContentInterface, BodyInterface
{
    public function getExpression(): ExpressionInterface;
}
