<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Step;

use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedStatementException;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedStepException;
use SmartAssert\Compiler\CompilableSource\Handler\Action\ActionHandler;
use SmartAssert\Compiler\CompilableSource\Handler\Assertion\AssertionHandler;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\EmptyLine;
use webignition\BasilModels\Model\Assertion\UniqueAssertionCollection;
use webignition\BasilModels\Model\Step\StepInterface;

class StepHandler
{
    public function __construct(
        private ActionHandler $actionHandler,
        private AssertionHandler $assertionHandler,
        private StatementBlockFactory $statementBlockFactory,
        private DerivedAssertionFactory $derivedAssertionFactory
    ) {
    }

    public static function createHandler(): StepHandler
    {
        return new StepHandler(
            ActionHandler::createHandler(),
            AssertionHandler::createHandler(),
            StatementBlockFactory::createFactory(),
            DerivedAssertionFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedStepException
     */
    public function handle(StepInterface $step): BodyInterface
    {
        $bodySources = [];

        try {
            foreach ($step->getActions() as $action) {
                try {
                    $derivedActionAssertions = $this->derivedAssertionFactory->createForAction($action);
                    $bodySources[] = $this->createDerivedAssertionsBody($derivedActionAssertions);
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($action, $unsupportedContentException);
                }

                $bodySources[] = $this->statementBlockFactory->create($action);
                $bodySources[] = $this->actionHandler->handle($action);
                $bodySources[] = new EmptyLine();
            }

            $stepAssertions = $step->getAssertions();

            $derivedAssertionAssertions = new UniqueAssertionCollection();
            foreach ($stepAssertions as $assertion) {
                try {
                    $derivedAssertionAssertions = $derivedAssertionAssertions->merge(
                        $this->derivedAssertionFactory->createForAssertion($assertion)
                    );
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($assertion, $unsupportedContentException);
                }
            }

            $bodySources[] = $this->createDerivedAssertionsBody($derivedAssertionAssertions);

            foreach ($stepAssertions as $assertion) {
                $bodySources[] = $this->statementBlockFactory->create($assertion);
                $bodySources[] = $this->assertionHandler->handle($assertion);
                $bodySources[] = new EmptyLine();
            }
        } catch (UnsupportedStatementException $unsupportedStatementException) {
            throw new UnsupportedStepException($step, $unsupportedStatementException);
        }

        return new Body($bodySources);
    }

    /**
     * @throws UnsupportedStatementException
     */
    private function createDerivedAssertionsBody(UniqueAssertionCollection $assertions): BodyInterface
    {
        $derivedAssertionBlockSources = [];
        foreach ($assertions as $assertion) {
            $derivedAssertionBlockSources[] = $this->statementBlockFactory->create($assertion);
            $derivedAssertionBlockSources[] = $this->assertionHandler->handle($assertion);
        }

        if ([] !== $derivedAssertionBlockSources) {
            $derivedAssertionBlockSources[] = new EmptyLine();
        }

        return new Body($derivedAssertionBlockSources);
    }
}
