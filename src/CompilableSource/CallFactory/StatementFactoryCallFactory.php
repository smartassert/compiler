<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\CallFactory;

use SmartAssert\Compiler\CompilableSource\ArgumentFactory;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\StatementInterface;

class StatementFactoryCallFactory
{
    public function __construct(
        private ArgumentFactory $argumentFactory
    ) {
    }

    public static function createFactory(): self
    {
        return new StatementFactoryCallFactory(
            ArgumentFactory::createFactory()
        );
    }

    public function create(StatementInterface $statement): ObjectMethodInvocation
    {
        $objectPlaceholderName = $statement instanceof AssertionInterface
            ? VariableNames::ASSERTION_FACTORY
            : VariableNames::ACTION_FACTORY;

        $serializedStatementSource = (string) json_encode($statement, JSON_PRETTY_PRINT);

        return new ObjectMethodInvocation(
            new VariableDependency($objectPlaceholderName),
            'createFromJson',
            new MethodArguments(
                $this->argumentFactory->create($serializedStatementSource)
            )
        );
    }
}
