<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\MethodInvocationInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;

class AssertionMethodInvocationFactory
{
    public static function createFactory(): AssertionMethodInvocationFactory
    {
        return new AssertionMethodInvocationFactory();
    }

    public function create(
        string $assertionMethod,
        ?MethodArgumentsInterface $arguments = null
    ): MethodInvocationInterface {
        if ($arguments instanceof MethodArgumentsInterface) {
            $arguments = new MethodArguments($arguments->getArguments(), MethodArguments::FORMAT_STACKED);
        }

        return new ObjectMethodInvocation(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            $assertionMethod,
            $arguments
        );
    }
}
