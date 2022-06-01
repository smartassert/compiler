<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodInvocation;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;

class MethodInvocation implements MethodInvocationInterface
{
    private const RENDER_TEMPLATE = '{{ call }}({{ arguments }})';

    private string $methodName;
    private MethodArgumentsInterface $arguments;

    public function __construct(string $methodName, ?MethodArgumentsInterface $arguments = null)
    {
        $this->methodName = $methodName;
        $this->arguments = $arguments ?? new MethodArguments([]);
    }

    public function getCall(): string
    {
        return $this->methodName;
    }

    public function getArguments(): MethodArgumentsInterface
    {
        return $this->arguments;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->arguments->getMetadata();
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'call' => $this->getCall(),
            'arguments' => $this->arguments,
        ];
    }
}
