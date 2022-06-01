<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Assertion;

use SmartAssert\Compiler\CompilableSource\ArgumentFactory;
use SmartAssert\Compiler\CompilableSource\AssertionMethodInvocationFactory;
use SmartAssert\Compiler\CompilableSource\CallFactory\DomCrawlerNavigatorCallFactory;
use SmartAssert\Compiler\CompilableSource\CallFactory\ElementIdentifierCallFactory;
use SmartAssert\Compiler\CompilableSource\ElementIdentifierSerializer;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Handler\DomIdentifierHandler;
use SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch\CatchBlock;
use SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch\TryBlock;
use SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch\TryCatchBlock;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\ClassName;
use SmartAssert\Compiler\CompilableSource\Model\Expression\AssignmentExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\CatchExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ComparisonExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\EncapsulatedExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\LiteralExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ObjectPropertyAccessExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArgumentsInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\StaticObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\Model\StaticObject;
use SmartAssert\Compiler\CompilableSource\Model\TypeDeclaration\ObjectTypeDeclaration;
use SmartAssert\Compiler\CompilableSource\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\Model\VariableName;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

class IdentifierExistenceAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private DomIdentifierHandler $domIdentifierHandler,
        private ElementIdentifierCallFactory $elementIdentifierCallFactory,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private ArgumentFactory $argumentFactory
    ) {
        parent::__construct($assertionMethodInvocationFactory);
    }

    public static function createHandler(): self
    {
        return new IdentifierExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            ElementIdentifierCallFactory::createFactory(),
            ElementIdentifierSerializer::createSerializer(),
            ArgumentFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $identifier = $assertion->getIdentifier();

        $assertionStatement = $this->createAssertionStatement(
            $assertion,
            new MethodArguments([
                $this->createGetBooleanExaminedValueInvocation()
            ])
        );

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString((string) $identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $serializedElementIdentifier = $this->elementIdentifierSerializer->serialize($domIdentifier);
        $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall(
            $serializedElementIdentifier
        );

        $examinedElementIdentifierPlaceholder = new ObjectPropertyAccessExpression(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            'examinedElementIdentifier'
        );

        $domNavigatorCrawlerCall = $this->createDomCrawlerNavigatorCall(
            $domIdentifier,
            $assertion,
            $examinedElementIdentifierPlaceholder
        );

        $elementSetBooleanExaminedValueInvocation = $this->createSetBooleanExaminedValueInvocation(
            new MethodArguments(
                [
                    $domNavigatorCrawlerCall
                ],
                MethodArguments::FORMAT_STACKED
            )
        );

        if (!$domIdentifier instanceof AttributeIdentifierInterface) {
            return new Body([
                new Statement(
                    new AssignmentExpression($examinedElementIdentifierPlaceholder, $elementIdentifierExpression)
                ),
                $this->createNavigatorHasCallTryCatchBlock($elementSetBooleanExaminedValueInvocation),
                $assertionStatement,
            ]);
        }

        $elementIdentifierString = (string) ElementIdentifier::fromAttributeIdentifier($domIdentifier);
        $elementExistsAssertion = new Assertion(
            $elementIdentifierString . ' exists',
            $elementIdentifierString,
            'exists'
        );

        $attributeNullComparisonExpression = new ComparisonExpression(
            $this->domIdentifierHandler->handleAttributeValue(
                $this->elementIdentifierSerializer->serialize($domIdentifier),
                $domIdentifier->getAttributeName()
            ),
            new LiteralExpression('null'),
            '??'
        );

        $attributeSetBooleanExaminedValueInvocation = $this->createSetBooleanExaminedValueInvocation(
            new MethodArguments([
                new ComparisonExpression(
                    new EncapsulatedExpression($attributeNullComparisonExpression),
                    new LiteralExpression('null'),
                    '!=='
                ),
            ])
        );

        return new Body([
            new Statement(
                new AssignmentExpression($examinedElementIdentifierPlaceholder, $elementIdentifierExpression)
            ),
            $this->createNavigatorHasCallTryCatchBlock($elementSetBooleanExaminedValueInvocation),
            $this->createAssertionStatement(
                $elementExistsAssertion,
                new MethodArguments([
                    $this->createGetBooleanExaminedValueInvocation()
                ])
            ),
            new Statement($attributeSetBooleanExaminedValueInvocation),
            $assertionStatement,
        ]);
    }

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }

    private function createSetBooleanExaminedValueInvocation(MethodArgumentsInterface $arguments): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('setBooleanExaminedValue', $arguments);
    }

    private function createGetBooleanExaminedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExaminedValue');
    }

    private function createDomCrawlerNavigatorCall(
        ElementIdentifierInterface $domIdentifier,
        AssertionInterface $assertion,
        ObjectPropertyAccessExpression $expression
    ): ExpressionInterface {
        $isAttributeIdentifier = $domIdentifier instanceof AttributeIdentifierInterface;
        $isDerivedFromInteractionAction = false;

        if ($assertion instanceof DerivedValueOperationAssertion) {
            $sourceStatement = $assertion->getSourceStatement();

            $isDerivedFromInteractionAction =
                $sourceStatement instanceof ActionInterface && $sourceStatement->isInteraction();
        }

        return $isAttributeIdentifier || $isDerivedFromInteractionAction
                ? $this->domCrawlerNavigatorCallFactory->createHasOneCall($expression)
                : $this->domCrawlerNavigatorCallFactory->createHasCall($expression);
    }

    private function createNavigatorHasCallTryCatchBlock(
        ExpressionInterface $elementSetBooleanExaminedValueInvocation
    ): TryCatchBlock {
        return new TryCatchBlock(
            new TryBlock(
                Body::createFromExpressions([$elementSetBooleanExaminedValueInvocation])
            ),
            new CatchBlock(
                new CatchExpression(
                    new ObjectTypeDeclarationCollection([
                        new ObjectTypeDeclaration(new ClassName(InvalidLocatorException::class))
                    ])
                ),
                Body::createFromExpressions([
                    new StaticObjectMethodInvocation(
                        new StaticObject('self'),
                        'staticSetLastException',
                        new MethodArguments([
                            new VariableName('exception')
                        ])
                    ),
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                        'fail',
                        new MethodArguments($this->argumentFactory->create('Invalid locator'))
                    )
                ])
            )
        );
    }
}
