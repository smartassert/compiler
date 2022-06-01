<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler;

use SmartAssert\Compiler\CompilableSource\ArgumentFactory;
use SmartAssert\Compiler\CompilableSource\CallFactory\DomCrawlerNavigatorCallFactory;
use SmartAssert\Compiler\CompilableSource\CallFactory\ElementIdentifierCallFactory;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\EmptyLine;
use SmartAssert\Compiler\CompilableSource\Model\Expression\AssignmentExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ClosureExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ReturnExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\Model\VariableName;
use SmartAssert\Compiler\CompilableSource\VariableNames;

class DomIdentifierHandler
{
    public function __construct(
        private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        private ElementIdentifierCallFactory $elementIdentifierCallFactory,
        private ArgumentFactory $argumentFactory
    ) {
    }

    public static function createHandler(): DomIdentifierHandler
    {
        return new DomIdentifierHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementIdentifierCallFactory::createFactory(),
            ArgumentFactory::createFactory()
        );
    }

    public function handleElement(string $serializedElementIdentifier): ExpressionInterface
    {
        return $this->domCrawlerNavigatorCallFactory->createFindOneCall(
            $this->elementIdentifierCallFactory->createConstructorCall($serializedElementIdentifier)
        );
    }

    public function handleElementCollection(string $serializedElementIdentifier): ExpressionInterface
    {
        return $this->domCrawlerNavigatorCallFactory->createFindCall(
            $this->elementIdentifierCallFactory->createConstructorCall($serializedElementIdentifier)
        );
    }

    public function handleAttributeValue(
        string $serializedElementIdentifier,
        string $attributeName
    ): ExpressionInterface {
        $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall(
            $serializedElementIdentifier
        );

        $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCall($elementIdentifierExpression);

        $elementPlaceholder = new VariableName('element');

        $closureExpressionStatements = [
            new Statement(
                new AssignmentExpression($elementPlaceholder, $findCall)
            ),
            new EmptyLine(),
            new Statement(
                new ReturnExpression(
                    new ObjectMethodInvocation(
                        $elementPlaceholder,
                        'getAttribute',
                        new MethodArguments($this->argumentFactory->create($attributeName))
                    )
                )
            ),
        ];

        return new ClosureExpression(new Body($closureExpressionStatements));
    }

    public function handleElementValue(string $serializedElementIdentifier): ExpressionInterface
    {
        $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall(
            $serializedElementIdentifier
        );

        $findCall = $this->domCrawlerNavigatorCallFactory->createFindCall($elementIdentifierExpression);

        $elementPlaceholder = new VariableName('element');

        $closureExpressionStatements = [
            new Statement(
                new AssignmentExpression($elementPlaceholder, $findCall)
            ),
            new EmptyLine(),
            new Statement(
                new ReturnExpression(
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR),
                        'getValue',
                        new MethodArguments([
                            $elementPlaceholder,
                        ])
                    )
                )
            )
        ];

        return new ClosureExpression(new Body($closureExpressionStatements));
    }
}
