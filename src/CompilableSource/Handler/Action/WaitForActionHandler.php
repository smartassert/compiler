<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Action;

use SmartAssert\Compiler\CompilableSource\ArgumentFactory;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;

class WaitForActionHandler
{
    public function __construct(
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser,
        private ArgumentFactory $argumentFactory
    ) {
    }

    public static function createHandler(): WaitForActionHandler
    {
        return new WaitForActionHandler(
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            ArgumentFactory::createFactory(),
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        $identifier = (string) $action->getIdentifier();

        if (!$this->identifierTypeAnalyser->isDomIdentifier($identifier)) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        if ($domIdentifier instanceof AttributeIdentifierInterface) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        return Body::createForSingleAssignmentStatement(
            new VariableDependency(VariableNames::PANTHER_CRAWLER),
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PANTHER_CLIENT),
                'waitFor',
                new MethodArguments(
                    $this->argumentFactory->create($domIdentifier->getLocator())
                )
            )
        );
    }
}
