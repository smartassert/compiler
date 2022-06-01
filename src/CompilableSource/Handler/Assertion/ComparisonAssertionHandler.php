<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Assertion;

use SmartAssert\Compiler\CompilableSource\AssertionMethodInvocationFactory;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedStatementException;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\CastExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\ValueAccessorFactory;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class ComparisonAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_EQUALS_METHOD = 'assertEquals';
    public const ASSERT_NOT_EQUALS_METHOD = 'assertNotEquals';
    public const ASSERT_STRING_CONTAINS_STRING_METHOD = 'assertStringContainsString';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_METHOD = 'assertStringNotContainsString';
    public const ASSERT_MATCHES_METHOD = 'assertMatchesRegularExpression';

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        'excludes' => self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
        'is' => self::ASSERT_EQUALS_METHOD,
        'is-not' => self::ASSERT_NOT_EQUALS_METHOD,
        'matches' => self::ASSERT_MATCHES_METHOD,
    ];

    /**
     * @var string[]
     */
    private array $methodsWithStringArguments = [
        self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        private ValueAccessorFactory $valueAccessorFactory
    ) {
        parent::__construct($assertionMethodInvocationFactory);
    }

    public static function createHandler(): self
    {
        return new ComparisonAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            ValueAccessorFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     * @throws UnsupportedStatementException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        if (!$assertion->isComparison()) {
            throw new UnsupportedStatementException($assertion);
        }

        $assertionMethod = self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$assertion->getOperator()];

        $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $assertion->getIdentifier());
        $expectedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull((string) $assertion->getValue());

        $assertionArguments = [
            $this->createPhpUnitTestCaseObjectMethodInvocation('getExpectedValue'),
            $this->createPhpUnitTestCaseObjectMethodInvocation('getExaminedValue'),
        ];

        $isStringArgumentAssertionMethod = in_array($assertionMethod, $this->methodsWithStringArguments);
        if ($isStringArgumentAssertionMethod) {
            array_walk($assertionArguments, function (ExpressionInterface &$expression) {
                $expression = new CastExpression($expression, 'string');
            });
        }

        return new Body([
            new Statement(
                $this->createPhpUnitTestCaseObjectMethodInvocation(
                    'setExpectedValue',
                    new MethodArguments([$expectedAccessor])
                )
            ),
            new Statement(
                $this->createPhpUnitTestCaseObjectMethodInvocation(
                    'setExaminedValue',
                    new MethodArguments([$examinedAccessor])
                )
            ),
            $this->createAssertionStatement($assertion, new MethodArguments($assertionArguments)),
        ]);
    }

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }
}
