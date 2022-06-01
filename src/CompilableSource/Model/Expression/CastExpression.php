<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class CastExpression implements ExpressionInterface
{
    private const RENDER_TEMPLATE = '({{ cast_type }}) {{ expression }}';

    private ExpressionInterface $expression;
    private string $castTo;

    public function __construct(ExpressionInterface $expression, string $castTo)
    {
        $this->expression = new EncapsulatedExpression($expression);
        $this->castTo = $castTo;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'cast_type' => $this->castTo,
            'expression' => $this->expression,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->expression->getMetadata();
    }
}
