<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Block;

use SmartAssert\Compiler\CompilableSource\Model\ClassName;

class RenderableClassDependencyCollection extends ClassDependencyCollection
{
    public function __construct(array $classNames = [])
    {
        $renderableClassNames = array_filter($classNames, function (ClassName $className) {
            if (false === $className->isInRootNamespace()) {
                return true;
            }

            return is_string($className->getAlias());
        });

        parent::__construct($renderableClassNames);
    }
}
