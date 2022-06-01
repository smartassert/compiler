<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;

interface VariablePlaceholderInterface extends ExpressionInterface
{
    public function getName(): string;
}
