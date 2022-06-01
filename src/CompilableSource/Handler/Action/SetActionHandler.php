<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Action;

use SmartAssert\Compiler\CompilableSource\AccessorDefaultValueFactory;
use SmartAssert\Compiler\CompilableSource\ElementIdentifierSerializer;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Handler\DomIdentifierHandler;
use SmartAssert\Compiler\CompilableSource\Handler\Value\ScalarValueHandler;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ComparisonExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\LiteralExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class SetActionHandler
{
    public function __construct(
        private ScalarValueHandler $scalarValueHandler,
        private DomIdentifierHandler $domIdentifierHandler,
        private AccessorDefaultValueFactory $accessorDefaultValueFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ElementIdentifierSerializer $elementIdentifierSerializer
    ) {
    }

    public static function createHandler(): self
    {
        return new SetActionHandler(
            ScalarValueHandler::createHandler(),
            DomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            ElementIdentifierSerializer::createSerializer()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        $identifier = (string) $action->getIdentifier();

        if (!$this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $value = (string) $action->getValue();
        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $collectionAccessor = $this->domIdentifierHandler->handleElementCollection(
            trim($this->elementIdentifierSerializer->serialize($domIdentifier))
        );

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
            $valueDomIdentifier = $this->domIdentifierFactory->createFromIdentifierString($value);
            if (null === $valueDomIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $value);
            }

            if ($valueDomIdentifier instanceof AttributeIdentifierInterface) {
                $valueAccessor = $this->domIdentifierHandler->handleAttributeValue(
                    $this->elementIdentifierSerializer->serialize($valueDomIdentifier),
                    (string) $valueDomIdentifier->getAttributeName()
                );
            } else {
                $valueAccessor = $this->domIdentifierHandler->handleElementValue(
                    $this->elementIdentifierSerializer->serialize($valueDomIdentifier)
                );
            }
        } else {
            $valueAccessor = $this->scalarValueHandler->handle($value);
        }

        $defaultValue = $this->accessorDefaultValueFactory->createString($value);
        if (null !== $defaultValue) {
            $valueAccessor = new ComparisonExpression(
                $valueAccessor,
                new LiteralExpression((string) $this->accessorDefaultValueFactory->createString($value)),
                '??'
            );
        }

        $mutationInvocation = new ObjectMethodInvocation(
            new VariableDependency(VariableNames::WEBDRIVER_ELEMENT_MUTATOR),
            'setValue',
            new MethodArguments(
                [
                    $collectionAccessor,
                    $valueAccessor
                ],
                MethodArguments::FORMAT_STACKED
            )
        );

        return Body::createFromExpressions([$mutationInvocation]);
    }
}
