<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Handler\DomIdentifierHandler;
use SmartAssert\Compiler\CompilableSource\Handler\Value\ScalarValueHandler;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ClosureExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ComparisonExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\LiteralExpression;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class ValueAccessorFactory
{
    public function __construct(
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private DomIdentifierFactory $domIdentifierFactory,
        private DomIdentifierHandler $domIdentifierHandler,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private ScalarValueHandler $scalarValueHandler,
        private AccessorDefaultValueFactory $accessorDefaultValueFactory
    ) {
    }

    public static function createFactory(): self
    {
        return new ValueAccessorFactory(
            IdentifierTypeAnalyser::create(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            ElementIdentifierSerializer::createSerializer(),
            ScalarValueHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function create(string $value): ExpressionInterface
    {
        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
            $identifier = $this->domIdentifierFactory->createFromIdentifierString($value);
            if (null === $identifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $value);
            }

            if ($identifier instanceof AttributeIdentifierInterface) {
                return $this->domIdentifierHandler->handleAttributeValue(
                    $this->elementIdentifierSerializer->serialize($identifier),
                    $identifier->getAttributeName()
                );
            }

            return $this->domIdentifierHandler->handleElementValue(
                $this->elementIdentifierSerializer->serialize($identifier)
            );
        }

        return $this->scalarValueHandler->handle($value);
    }

    /**
     * @throws UnsupportedContentException
     */
    public function createWithDefaultIfNull(string $value): ExpressionInterface
    {
        $accessor = $this->create($value);

        if (!$accessor instanceof ClosureExpression) {
            $defaultValue = $this->accessorDefaultValueFactory->createString($value) ?? 'null';

            $accessor = new ComparisonExpression(
                $accessor,
                new LiteralExpression($defaultValue),
                '??'
            );
        }

        return $accessor;
    }
}
