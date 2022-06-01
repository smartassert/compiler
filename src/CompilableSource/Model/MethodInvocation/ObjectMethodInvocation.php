<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodInvocation;

use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;

class ObjectMethodInvocation extends AbstractMethodInvocationEncapsulator implements MethodInvocationInterface
{
    private const RENDER_TEMPLATE = '{{ object }}->{{ method_invocation }}';

    private ExpressionInterface $object;

    public function __construct(
        ExpressionInterface $object,
        string $methodName,
        ?MethodArgumentsInterface $arguments = null
    ) {
        parent::__construct($methodName, $arguments);
        $this->object = $object;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'object' => $this->object,
            'method_invocation' => $this->invocation,
        ];
    }

    protected function getAdditionalMetadata(): MetadataInterface
    {
        return $this->object->getMetadata();
    }
}
