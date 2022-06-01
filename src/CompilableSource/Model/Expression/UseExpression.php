<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\ClassName;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class UseExpression implements ExpressionInterface
{
    private const RENDER_TEMPLATE = 'use {{ class_name }}';

    private ClassName $className;

    public function __construct(ClassName $className)
    {
        $this->className = $className;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'class_name' => $this->renderClassName(),
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }

    private function renderClassName(): string
    {
        $content = $this->className->getClassName();
        $alias = $this->className->getAlias();

        if (is_string($alias)) {
            $content .= ' as ' . $alias;
        }

        return $content;
    }
}
