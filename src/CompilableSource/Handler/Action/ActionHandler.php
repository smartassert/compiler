<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Action;

use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedStatementException;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilModels\Model\Action\ActionInterface;

class ActionHandler
{
    public function __construct(
        private BrowserOperationActionHandler $browserOperationActionHandler,
        private InteractionActionHandler $interactionActionHandler,
        private SetActionHandler $setActionHandler,
        private WaitActionHandler $waitActionHandler,
        private WaitForActionHandler $waitForActionHandler
    ) {
    }

    public static function createHandler(): ActionHandler
    {
        return new ActionHandler(
            BrowserOperationActionHandler::createHandler(),
            InteractionActionHandler::createHandler(),
            SetActionHandler::createHandler(),
            WaitActionHandler::createHandler(),
            WaitForActionHandler::createHandler()
        );
    }

    /**
     * @throws UnsupportedStatementException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        try {
            if (in_array($action->getType(), ['back', 'forward', 'reload'])) {
                return $this->addRefreshCrawlerAndNavigatorStatement(
                    $this->browserOperationActionHandler->handle($action)
                );
            }

            if ($action->isInteraction()) {
                if (in_array($action->getType(), ['click', 'submit'])) {
                    return $this->addRefreshCrawlerAndNavigatorStatement(
                        $this->interactionActionHandler->handle($action)
                    );
                }

                if (in_array($action->getType(), ['wait-for'])) {
                    return $this->waitForActionHandler->handle($action);
                }
            }

            if ($action->isInput()) {
                return $this->addRefreshCrawlerAndNavigatorStatement($this->setActionHandler->handle($action));
            }

            if ($action->isWait()) {
                return $this->waitActionHandler->handle($action);
            }
        } catch (UnsupportedContentException $unsupportedContentException) {
            throw new UnsupportedStatementException($action, $unsupportedContentException);
        }

        throw new UnsupportedStatementException($action);
    }

    private function addRefreshCrawlerAndNavigatorStatement(BodyInterface $body): BodyInterface
    {
        return new Body([
            $body,
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                    'refreshCrawlerAndNavigator'
                )
            ),
        ]);
    }
}
