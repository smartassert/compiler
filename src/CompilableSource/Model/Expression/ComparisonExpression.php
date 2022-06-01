<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class ComparisonExpression implements ExpressionInterface
{
    private const RENDER_TEMPLATE = '{{ left_hand_side }} {{ comparison}} {{ right_hand_side }}';

    private ExpressionInterface $leftHandSide;
    private ExpressionInterface $rightHandSide;
    private string $comparison;

    public function __construct(
        ExpressionInterface $leftHandSide,
        ExpressionInterface $rightHandSide,
        string $comparison
    ) {
        $this->leftHandSide = $leftHandSide;
        $this->rightHandSide = $rightHandSide;
        $this->comparison = $comparison;
    }

    public function getLeftHandSide(): ExpressionInterface
    {
        return $this->leftHandSide;
    }

    public function getRightHandSide(): ExpressionInterface
    {
        return $this->rightHandSide;
    }

    public function getComparison(): string
    {
        return $this->comparison;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'left_hand_side' => $this->leftHandSide,
            'comparison' => $this->comparison,
            'right_hand_side' => $this->rightHandSide,
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();
        $metadata = $metadata->merge($this->leftHandSide->getMetadata());

        return $metadata->merge($this->rightHandSide->getMetadata());
    }
}
