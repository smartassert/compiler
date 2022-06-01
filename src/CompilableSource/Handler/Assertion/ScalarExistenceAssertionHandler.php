<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Assertion;

use SmartAssert\Compiler\CompilableSource\AssertionMethodInvocationFactory;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Handler\Value\ScalarValueHandler;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ComparisonExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\EncapsulatedExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\LiteralExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class ScalarExistenceAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        private ScalarValueHandler $scalarValueHandler
    ) {
        parent::__construct($assertionMethodInvocationFactory);
    }

    public static function createHandler(): self
    {
        return new ScalarExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            ScalarValueHandler::createHandler()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $nullComparisonExpression = new ComparisonExpression(
            $this->scalarValueHandler->handle((string) $assertion->getIdentifier()),
            new LiteralExpression('null'),
            '??'
        );

        $setBooleanExaminedValueInvocation = $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setBooleanExaminedValue',
            new MethodArguments([
                new ComparisonExpression(
                    new EncapsulatedExpression($nullComparisonExpression),
                    new LiteralExpression('null'),
                    '!=='
                ),
            ])
        );

        $assertionStatement = $this->createAssertionStatement(
            $assertion,
            new MethodArguments([
                $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExaminedValue')
            ])
        );

        return new Body([
            new Statement($setBooleanExaminedValueInvocation),
            $assertionStatement,
        ]);
    }

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }
}
