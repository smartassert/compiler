<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Annotation;

use SmartAssert\Compiler\CompilableSource\Model\VariableName;

class ParameterAnnotation extends AbstractAnnotation implements AnnotationInterface
{
    public function __construct(string $type, VariableName $name)
    {
        parent::__construct('param', [$type, (string) $name]);
    }
}
