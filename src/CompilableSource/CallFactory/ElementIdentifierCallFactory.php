<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\CallFactory;

use SmartAssert\Compiler\CompilableSource\ArgumentFactory;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\StaticObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\StaticObject;
use webignition\DomElementIdentifier\ElementIdentifier;

class ElementIdentifierCallFactory
{
    public function __construct(
        private ArgumentFactory $argumentFactory
    ) {
    }

    public static function createFactory(): ElementIdentifierCallFactory
    {
        return new ElementIdentifierCallFactory(
            ArgumentFactory::createFactory()
        );
    }

    public function createConstructorCall(string $serializedSourceIdentifier): ExpressionInterface
    {
        return new StaticObjectMethodInvocation(
            new StaticObject(ElementIdentifier::class),
            'fromJson',
            new MethodArguments(
                $this->argumentFactory->create($serializedSourceIdentifier)
            )
        );
    }
}
