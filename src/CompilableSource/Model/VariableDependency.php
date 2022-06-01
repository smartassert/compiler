<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class VariableDependency implements ExpressionInterface, VariableDependencyInterface
{
    private const RENDER_TEMPLATE = '{{ {{ name }} }}';

    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): MetadataInterface
    {
        $placeholderCollection = new VariableDependencyCollection();
        $placeholderCollection->add($this);

        return new Metadata([
            Metadata::KEY_VARIABLE_DEPENDENCIES => $placeholderCollection,
        ]);
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
