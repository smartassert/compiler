<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver;

use SmartAssert\Compiler\Loader\Resolver\StatementComponentResolver\ComponentElementResolver;
use SmartAssert\Compiler\Loader\Resolver\StatementComponentResolver\ComponentUrlResolver;
use SmartAssert\Compiler\Loader\Resolver\StatementComponentResolver\StatementComponentResolverInterface;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Action\ResolvedAction;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\Assertion\ResolvedAssertion;
use webignition\BasilModels\Model\Statement\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Identifier\IdentifierProviderInterface;
use webignition\BasilModels\Provider\Page\PageProviderInterface;

class StatementResolver
{
    /**
     * @param StatementComponentResolverInterface[] $componentResolvers
     */
    final public function __construct(
        private array $componentResolvers
    ) {}

    public static function createResolver(): static
    {
        return new static([
            ComponentElementResolver::createResolver(),
            ComponentUrlResolver::createResolver(),
        ]);
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    public function resolve(
        StatementInterface $statement,
        PageProviderInterface $pageProvider,
        IdentifierProviderInterface $identifierProvider
    ): StatementInterface {
        $resolvedIdentifier = null;
        $resolvedValue = null;

        foreach ($this->componentResolvers as $componentResolver) {
            $resolvedComponent = $componentResolver->resolve(
                $statement->getIdentifier(),
                $pageProvider,
                $identifierProvider
            );

            if ($resolvedComponent && $resolvedComponent->isResolved()) {
                $resolvedIdentifier = $resolvedComponent->getResolved();
            }

            $resolvedComponent = $componentResolver->resolve(
                $statement->getValue(),
                $pageProvider,
                $identifierProvider
            );

            if ($resolvedComponent && $resolvedComponent->isResolved()) {
                $resolvedValue = $resolvedComponent->getResolved();
            }
        }

        $resolvedStatement = $this->createResolvedStatement($statement, $resolvedIdentifier, $resolvedValue);

        return $resolvedStatement ?? $statement;
    }

    private function createResolvedStatement(
        StatementInterface $statement,
        ?string $identifier,
        ?string $value
    ): ?EncapsulatingStatementInterface {
        $identifierIsResolved = is_string($identifier);
        $valueIsResolved = is_string($value);
        $isResolved = $identifierIsResolved || $valueIsResolved;

        if (false === $isResolved) {
            return null;
        }

        $newIdentifier = $identifier ?? $statement->getIdentifier();
        $newValue = $value ?? $statement->getValue();

        if ($statement instanceof ActionInterface) {
            return new ResolvedAction($statement, $newIdentifier, $newValue);
        }

        if ($statement instanceof AssertionInterface) {
            return new ResolvedAssertion($statement, (string) $newIdentifier, $newValue);
        }

        return null;
    }
}
