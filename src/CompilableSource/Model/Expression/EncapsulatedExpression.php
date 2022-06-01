<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class EncapsulatedExpression implements ExpressionInterface
{
    private const RENDER_TEMPLATE = '({{ expression }})';

    private ExpressionInterface $expression;

    public function __construct(ExpressionInterface $expression)
    {
        $this->expression = $expression;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'expression' => $this->expression,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->expression->getMetadata();
    }
}
