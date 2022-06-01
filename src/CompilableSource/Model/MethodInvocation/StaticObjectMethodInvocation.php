<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodInvocation;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\StaticObjectMethodInvocationInterface as SInterface;
use SmartAssert\Compiler\CompilableSource\Model\StaticObject;

class StaticObjectMethodInvocation extends AbstractMethodInvocationEncapsulator implements SInterface
{
    private const RENDER_TEMPLATE = '{{ object }}::{{ method_invocation }}';

    private StaticObject $staticObject;

    public function __construct(
        StaticObject $staticObject,
        string $methodName,
        ?MethodArgumentsInterface $arguments = null
    ) {
        parent::__construct($methodName, $arguments);

        $this->staticObject = $staticObject;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'object' => $this->staticObject,
            'method_invocation' => $this->invocation,
        ];
    }

    public function getStaticObject(): StaticObject
    {
        return $this->staticObject;
    }

    protected function getAdditionalMetadata(): MetadataInterface
    {
        return $this->staticObject->getMetadata();
    }
}
