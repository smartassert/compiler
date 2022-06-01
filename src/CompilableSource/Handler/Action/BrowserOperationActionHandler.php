<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Action;

use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilModels\Model\Action\ActionInterface;

class BrowserOperationActionHandler
{
    public static function createHandler(): BrowserOperationActionHandler
    {
        return new BrowserOperationActionHandler();
    }

    public function handle(ActionInterface $action): BodyInterface
    {
        return Body::createForSingleAssignmentStatement(
            new VariableDependency(VariableNames::PANTHER_CRAWLER),
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PANTHER_CLIENT),
                $action->getType()
            )
        );
    }
}
