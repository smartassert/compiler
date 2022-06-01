<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Step;

use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\Assertion\UniqueAssertionCollection;
use webignition\BasilModels\Model\StatementInterface as StatementModelInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DerivedAssertionFactory
{
    public function __construct(
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
    }

    public static function createFactory(): self
    {
        return new DerivedAssertionFactory(
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function createForAction(ActionInterface $action): UniqueAssertionCollection
    {
        $assertions = new UniqueAssertionCollection();

        if ($action->isInteraction()) {
            $assertions = $assertions->merge($this->createForStatementAndAncestors(
                (string) $action->getIdentifier(),
                $action
            ));
        }

        if ($action->isInput()) {
            $assertions = $assertions->merge(
                $this->createForStatementAndAncestors((string) $action->getIdentifier(), $action)
            );

            $value = (string) $action->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $assertions = $assertions->merge($this->createForStatementAndAncestors($value, $action));
            }
        }

        if ($action->isWait()) {
            $duration = (string) $action->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($duration)) {
                $assertions = $assertions->merge($this->createForStatementAndAncestors($duration, $action));
            }
        }

        return $assertions;
    }

    /**
     * @throws UnsupportedContentException
     */
    public function createForAssertion(AssertionInterface $assertion): UniqueAssertionCollection
    {
        $assertions = new UniqueAssertionCollection();

        $assertions = $assertions->merge($this->createExistenceAssertionsForElementalComponents($assertion));

        return $assertions->merge($this->createRegexValidationAssertions($assertion));
    }

    /**
     * @throws UnsupportedContentException
     */
    private function createExistenceAssertionsForElementalComponents(
        AssertionInterface $assertion
    ): UniqueAssertionCollection {
        $assertions = new UniqueAssertionCollection();

        $isExistenceAssertion = in_array($assertion->getOperator(), ['exists', 'not-exists']);
        $identifier = $assertion->getIdentifier();

        if ($isExistenceAssertion) {
            if (is_string($identifier) && $this->identifierTypeAnalyser->isDescendantDomIdentifier($identifier)) {
                $assertions = $assertions->merge($this->createForStatementAncestorsOnly($identifier, $assertion));
            }
        } else {
            if (is_string($identifier) && $this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
                $assertions = $assertions->merge($this->createForStatementAndAncestors($identifier, $assertion));
            }
        }

        if ($assertion->isComparison()) {
            $value = (string) $assertion->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $assertions = $assertions->merge($this->createForStatementAndAncestors($value, $assertion));
            }
        }

        return $assertions;
    }

    private function createRegexValidationAssertions(AssertionInterface $assertion): UniqueAssertionCollection
    {
        $assertions = new UniqueAssertionCollection();

        if (!$assertion->isComparison()) {
            return $assertions;
        }

        if ('matches' !== $assertion->getOperator()) {
            return $assertions;
        }

        $assertions->add(new DerivedValueOperationAssertion($assertion, (string) $assertion->getValue(), 'is-regexp'));

        return $assertions;
    }

    /**
     * @throws UnsupportedContentException
     */
    private function createForStatementAndAncestors(
        string $identifier,
        StatementModelInterface $statement
    ): UniqueAssertionCollection {
        return $this->createForCollection(
            $identifier,
            $statement,
            function (ElementIdentifierInterface $domIdentifier): array {
                $elementHierarchy = $domIdentifier->getScope();
                $elementHierarchy[] = $domIdentifier;

                return $elementHierarchy;
            }
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    private function createForStatementAncestorsOnly(
        string $identifier,
        StatementModelInterface $statement
    ): UniqueAssertionCollection {
        return $this->createForCollection(
            $identifier,
            $statement,
            function (ElementIdentifierInterface $domIdentifier): array {
                return $domIdentifier->getScope();
            }
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    private function createForCollection(
        string $identifier,
        StatementModelInterface $action,
        callable $elementHierarchyCreator
    ): UniqueAssertionCollection {
        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementHierarchy = $elementHierarchyCreator($domIdentifier);

        $assertions = new UniqueAssertionCollection();

        foreach ($elementHierarchy as $elementIdentifier) {
            $assertions->add(new DerivedValueOperationAssertion($action, (string) $elementIdentifier, 'exists'));
        }

        return $assertions;
    }
}
