<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\CallFactory;

use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\VariableNames;

class DomCrawlerNavigatorCallFactory
{
    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory();
    }

    public function createFindCall(ExpressionInterface $elementIdentifierExpression): ExpressionInterface
    {
        return $this->createElementCall('find', $elementIdentifierExpression);
    }

    public function createFindOneCall(ExpressionInterface $elementIdentifierExpression): ExpressionInterface
    {
        return $this->createElementCall('findOne', $elementIdentifierExpression);
    }

    public function createHasCall(ExpressionInterface $elementIdentifierExpression): ExpressionInterface
    {
        return $this->createElementCall('has', $elementIdentifierExpression);
    }

    public function createHasOneCall(ExpressionInterface $elementIdentifierExpression): ExpressionInterface
    {
        return $this->createElementCall('hasOne', $elementIdentifierExpression);
    }

    private function createElementCall(
        string $methodName,
        ExpressionInterface $elementIdentifierExpression
    ): ExpressionInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableNames::DOM_CRAWLER_NAVIGATOR),
            $methodName,
            new MethodArguments([
                $elementIdentifierExpression,
            ])
        );
    }
}
