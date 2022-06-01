<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Action;

use SmartAssert\Compiler\CompilableSource\ElementIdentifierSerializer;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Handler\DomIdentifierHandler;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\AssignmentExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\Model\VariableName;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class InteractionActionHandler
{
    public function __construct(
        private DomIdentifierHandler $domIdentifierHandler,
        private DomIdentifierFactory $domIdentifierFactory,
        private ElementIdentifierSerializer $elementIdentifierSerializer
    ) {
    }

    public static function createHandler(): self
    {
        return new InteractionActionHandler(
            DomIdentifierHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            ElementIdentifierSerializer::createSerializer()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        $identifier = (string) $action->getIdentifier();

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementPlaceholder = new VariableName('element');

        $accessor = new Statement(
            new AssignmentExpression(
                $elementPlaceholder,
                $this->domIdentifierHandler->handleElement(
                    $this->elementIdentifierSerializer->serialize($domIdentifier)
                )
            )
        );

        $invocation = new Statement(new ObjectMethodInvocation(
            $elementPlaceholder,
            $action->getType()
        ));

        return Body::createEnclosingBody(new Body([
            $accessor,
            $invocation,
        ]));
    }
}
