<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver;

use webignition\BasilModels\Model\Statement\Action\ActionCollection;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollection;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Identifier\EmptyIdentifierProvider;
use webignition\BasilModels\Provider\Identifier\IdentifierProvider;
use webignition\BasilModels\Provider\Page\PageProviderInterface;

class StepResolver
{
    public function __construct(
        private readonly StatementResolver $statementResolver,
        private readonly ElementResolver $elementResolver
    ) {}

    public static function createResolver(): StepResolver
    {
        return new StepResolver(
            StatementResolver::createResolver(),
            ElementResolver::createResolver()
        );
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    public function resolve(StepInterface $step, PageProviderInterface $pageProvider): StepInterface
    {
        if ($step->requiresImportResolution()) {
            return $step;
        }

        $step = $this->resolveIdentifiers($step, $pageProvider);
        $step = $this->resolveActions($step, $pageProvider);

        return $this->resolveAssertions($step, $pageProvider);
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    private function resolveIdentifiers(StepInterface $step, PageProviderInterface $pageProvider): StepInterface
    {
        $resolvedIdentifiers = [];
        $identifierProvider = new EmptyIdentifierProvider();

        foreach ($step->getIdentifiers() as $name => $identifier) {
            $resolvedIdentifiers[$name] = $this->elementResolver->resolve(
                $identifier,
                $pageProvider,
                $identifierProvider
            );
        }

        return $step->withIdentifiers($resolvedIdentifiers);
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    private function resolveActions(StepInterface $step, PageProviderInterface $pageProvider): StepInterface
    {
        $resolvedActions = new ActionCollection([]);
        $identifierProvider = new IdentifierProvider($step->getIdentifiers());
        $action = null;

        try {
            foreach ($step->getActions() as $action) {
                $resolvedAction = $this->statementResolver->resolve($action, $pageProvider, $identifierProvider);
                if ($resolvedAction instanceof ActionInterface) {
                    $resolvedActions = $resolvedActions->append(new ActionCollection([$resolvedAction]));
                }
            }
        } catch (UnknownElementException | UnknownItemException | UnknownPageElementException $exception) {
            if ($action instanceof ActionInterface) {
                $exception->setContent($action->getSource());
            }

            throw $exception;
        }

        return $step->withActions($resolvedActions);
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    private function resolveAssertions(StepInterface $step, PageProviderInterface $pageProvider): StepInterface
    {
        $resolvedAssertions = new AssertionCollection([]);
        $identifierProvider = new IdentifierProvider($step->getIdentifiers());
        $assertion = null;

        try {
            foreach ($step->getAssertions() as $assertion) {
                $resolvedAssertion = $this->statementResolver->resolve(
                    $assertion,
                    $pageProvider,
                    $identifierProvider
                );

                if ($resolvedAssertion instanceof AssertionInterface) {
                    $resolvedAssertions = $resolvedAssertions->append(new AssertionCollection([$resolvedAssertion]));
                }
            }
        } catch (UnknownElementException | UnknownItemException | UnknownPageElementException $exception) {
            if ($assertion instanceof AssertionInterface) {
                $exception->setContent($assertion->getSource());
            }

            throw $exception;
        }

        return $step->withAssertions($resolvedAssertions);
    }
}
