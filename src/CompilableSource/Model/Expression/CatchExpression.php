<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use SmartAssert\Compiler\CompilableSource\Model\VariableName;

class CatchExpression implements ExpressionInterface
{
    private const RENDER_TEMPLATE = '{{ class_list }} {{ variable }}';

    private ObjectTypeDeclarationCollection $classes;

    public function __construct(ObjectTypeDeclarationCollection $classes)
    {
        $this->classes = $classes;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();

        return $metadata->merge($this->classes->getMetadata());
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'class_list' => $this->classes,
            'variable' => new VariableName('exception'),
        ];
    }
}
