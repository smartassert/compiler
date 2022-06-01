<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\HasMetadataInterface;
use webignition\StubbleResolvable\ResolvableInterface;

interface ExpressionInterface extends HasMetadataInterface, ResolvableInterface
{
}
