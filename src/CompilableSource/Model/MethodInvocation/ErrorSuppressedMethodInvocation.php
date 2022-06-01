<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodInvocation;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;

class ErrorSuppressedMethodInvocation implements MethodInvocationInterface
{
    private MethodInvocationInterface $invocation;

    public function __construct(MethodInvocationInterface $invocation)
    {
        $this->invocation = $invocation;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->invocation->getMetadata();
    }

    public function getCall(): string
    {
        return $this->invocation->getCall();
    }

    public function getArguments(): MethodArgumentsInterface
    {
        return $this->invocation->getArguments();
    }

    public function getTemplate(): string
    {
        return '@' . $this->invocation->getTemplate();
    }

    public function getContext(): array
    {
        return $this->invocation->getContext();
    }
}
