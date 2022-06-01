<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Value;

use SmartAssert\Compiler\CompilableSource\EnvironmentValueFactory;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\EmptyLine;
use SmartAssert\Compiler\CompilableSource\Model\Expression\AssignmentExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\CastExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ClosureExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\CompositeExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\LiteralExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ReturnExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\Model\VariableName;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ScalarValueHandler
{
    public function __construct(
        private ValueTypeIdentifier $valueTypeIdentifier,
        private EnvironmentValueFactory $environmentValueFactory
    ) {
    }

    public static function createHandler(): ScalarValueHandler
    {
        return new ScalarValueHandler(
            new ValueTypeIdentifier(),
            EnvironmentValueFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(string $value): ExpressionInterface
    {
        if ($this->valueTypeIdentifier->isBrowserProperty($value)) {
            return $this->handleBrowserProperty();
        }

        if ($this->valueTypeIdentifier->isDataParameter($value)) {
            $property = (string) preg_replace('/^\$data\./', '', $value);

            return new LiteralExpression('$' . $property);
        }

        if ($this->valueTypeIdentifier->isEnvironmentValue($value)) {
            return $this->handleEnvironmentValue($value);
        }

        if ($this->valueTypeIdentifier->isPageProperty($value)) {
            return $this->handlePageProperty($value);
        }

        if ($this->valueTypeIdentifier->isLiteralValue($value)) {
            return new LiteralExpression($value);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }

    private function handleBrowserProperty(): ExpressionInterface
    {
        $webDriverDimensionPlaceholder = new VariableName('webDriverDimension');

        return new ClosureExpression(new Body([
            new Statement(
                new AssignmentExpression(
                    $webDriverDimensionPlaceholder,
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNames::PANTHER_CLIENT),
                        'getWebDriver()->manage()->window()->getSize'
                    )
                )
            ),
            new EmptyLine(),
            new Statement(
                new ReturnExpression(
                    new CompositeExpression([
                        new CastExpression(
                            new ObjectMethodInvocation($webDriverDimensionPlaceholder, 'getWidth'),
                            'string'
                        ),
                        new LiteralExpression(' . \'x\' . '),
                        new CastExpression(
                            new ObjectMethodInvocation($webDriverDimensionPlaceholder, 'getHeight'),
                            'string'
                        ),
                    ])
                )
            ),
        ]));
    }

    private function handleEnvironmentValue(string $value): ExpressionInterface
    {
        $environmentValue = $this->environmentValueFactory->create($value);
        $property = $environmentValue->getProperty();

        return new CompositeExpression([
            new VariableDependency('ENV'),
            new LiteralExpression(sprintf('[\'%s\']', $property)),
        ]);
    }

    /**
     * @throws UnsupportedContentException
     *
     * @return ObjectMethodInvocation
     */
    private function handlePageProperty(string $value): ExpressionInterface
    {
        $property = (string) preg_replace('/^\$page\./', '', $value);

        $methodNameMap = [
            'title' => 'getTitle',
            'url' => 'getCurrentURL',
        ];

        $methodName = $methodNameMap[$property] ?? null;

        if (is_string($methodName)) {
            return new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PANTHER_CLIENT),
                $methodName
            );
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }
}
