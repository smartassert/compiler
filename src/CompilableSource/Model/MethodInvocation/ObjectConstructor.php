<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodInvocation;

use SmartAssert\Compiler\CompilableSource\Model\Block\ClassDependencyCollection;
use SmartAssert\Compiler\CompilableSource\Model\ClassName;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;

class ObjectConstructor extends AbstractMethodInvocationEncapsulator
{
    private const RENDER_TEMPLATE = 'new {{ method_invocation }}';

    private ClassName $class;

    public function __construct(ClassName $class, ?MethodArgumentsInterface $arguments = null)
    {
        parent::__construct($class->renderClassName(), $arguments);

        $this->class = $class;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'method_invocation' => $this->invocation,
        ];
    }

    protected function getAdditionalMetadata(): MetadataInterface
    {
        return new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                $this->class,
            ]),
        ]);
    }
}
