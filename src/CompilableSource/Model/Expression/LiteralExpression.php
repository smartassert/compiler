<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\ResolvableStringableTrait;

class LiteralExpression implements ExpressionInterface
{
    use ResolvableStringableTrait;

    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }
}
