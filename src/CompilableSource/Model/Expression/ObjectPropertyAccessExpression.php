<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\VariablePlaceholderInterface;

class ObjectPropertyAccessExpression implements ExpressionInterface
{
    private const RENDER_TEMPLATE = '{{ object }}->{{ property }}';

    private VariablePlaceholderInterface $objectPlaceholder;
    private string $property;

    public function __construct(VariablePlaceholderInterface $objectPlaceholder, string $property)
    {
        $this->objectPlaceholder = $objectPlaceholder;
        $this->property = $property;
    }

    public function getObjectPlaceholder(): VariablePlaceholderInterface
    {
        return $this->objectPlaceholder;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->objectPlaceholder->getMetadata();
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'object' => $this->objectPlaceholder,
            'property' => $this->property,
        ];
    }
}
