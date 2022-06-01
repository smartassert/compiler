<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Assertion;

use SmartAssert\Compiler\CompilableSource\ArgumentFactory;
use SmartAssert\Compiler\CompilableSource\AssertionMethodInvocationFactory;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ComparisonExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\LiteralExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ErrorSuppressedMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\MethodInvocation;
use SmartAssert\Compiler\CompilableSource\ValueAccessorFactory;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class IsRegExpAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'is-regexp' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ValueTypeIdentifier $valueTypeIdentifier,
        private ValueAccessorFactory $valueAccessorFactory,
        private ArgumentFactory $argumentFactory
    ) {
        parent::__construct($assertionMethodInvocationFactory);
    }

    public static function createHandler(): self
    {
        return new IsRegExpAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            ValueAccessorFactory::createFactory(),
            ArgumentFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $identifier = $assertion->getIdentifier();

        if (is_string($identifier) && $this->valueTypeIdentifier->isScalarValue($identifier)) {
            $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($identifier);

            return $this->createIsRegExpAssertionBody($examinedAccessor, $assertion);
        }

        if (is_string($identifier) && $this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
            if (null === $domIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
            }

            $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($identifier);

            return $this->createIsRegExpAssertionBody($examinedAccessor, $assertion);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }

    private function createIsRegExpAssertionBody(
        ExpressionInterface $examinedAccessor,
        AssertionInterface $assertion
    ): BodyInterface {
        $pregMatchInvocation = new ErrorSuppressedMethodInvocation(
            new MethodInvocation(
                'preg_match',
                new MethodArguments(
                    $this->argumentFactory->create(
                        $this->createPhpUnitTestCaseObjectMethodInvocation('getExaminedValue'),
                        null,
                    )
                )
            )
        );

        $identityComparison = new ComparisonExpression(
            $pregMatchInvocation,
            new LiteralExpression('false'),
            '==='
        );

        return new Body([
            Body::createFromExpressions([
                $this->createPhpUnitTestCaseObjectMethodInvocation(
                    'setExaminedValue',
                    new MethodArguments([$examinedAccessor])
                ),
                $this->createPhpUnitTestCaseObjectMethodInvocation(
                    'setBooleanExpectedValue',
                    new MethodArguments(
                        [
                            $identityComparison
                        ],
                        MethodArguments::FORMAT_STACKED
                    )
                ),
            ]),
            $this->createAssertionStatement(
                $assertion,
                new MethodArguments([
                    $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExpectedValue')
                ])
            ),
        ]);
    }
}
