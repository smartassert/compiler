<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class VariableName implements ExpressionInterface, VariablePlaceholderInterface
{
    use ResolvableStringableTrait;

    private string $name;
    private MetadataInterface $metadata;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->metadata = new Metadata();
    }

    public function __toString(): string
    {
        return '$' . $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
