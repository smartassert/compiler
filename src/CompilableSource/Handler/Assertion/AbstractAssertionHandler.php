<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Assertion;

use SmartAssert\Compiler\CompilableSource\AssertionMethodInvocationFactory;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\Model\Statement\StatementInterface;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

abstract class AbstractAssertionHandler
{
    public function __construct(
        private AssertionMethodInvocationFactory $assertionMethodInvocationFactory
    ) {
    }

    /**
     * @return array<string, string>
     */
    abstract protected function getOperationToAssertionTemplateMap(): array;

    protected function createAssertionStatement(
        AssertionInterface $assertion,
        ?MethodArgumentsInterface $arguments = null
    ): StatementInterface {
        return new Statement(
            $this->assertionMethodInvocationFactory->create(
                $this->getOperationToAssertionTemplateMap()[$assertion->getOperator()],
                $arguments
            )
        );
    }

    protected function createPhpUnitTestCaseObjectMethodInvocation(
        string $methodName,
        ?MethodArgumentsInterface $arguments = null
    ): ExpressionInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            $methodName,
            $arguments
        );
    }
}
