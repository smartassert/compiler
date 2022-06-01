<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Block\ClassDependencyCollection;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class StaticObject implements ExpressionInterface
{
    use ResolvableStringableTrait;

    private string $object;

    public function __construct(string $object)
    {
        $this->object = $object;
    }

    public function __toString(): string
    {
        if (ClassName::isFullyQualifiedClassName($this->object)) {
            $className = new ClassName($this->object);

            return $className->renderClassName();
        }

        return $this->object;
    }

    public function getMetadata(): MetadataInterface
    {
        if (ClassName::isFullyQualifiedClassName($this->object)) {
            return new Metadata([
                Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                    new ClassName($this->object),
                ]),
            ]);
        }

        return new Metadata();
    }
}
