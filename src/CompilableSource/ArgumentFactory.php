<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\LiteralExpression;

class ArgumentFactory
{
    public function __construct(
        private readonly SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
    }

    public static function createFactory(): self
    {
        return new ArgumentFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    /**
     * @param mixed ...$arguments
     *
     * @return ExpressionInterface[]
     */
    public function create(...$arguments): array
    {
        $expressionArguments = [];

        foreach ($arguments as $argument) {
            if (null === $argument) {
                $expressionArguments[] = new LiteralExpression('null');
            }

            if (is_bool($argument)) {
                $expressionArguments[] = new LiteralExpression($argument ? 'true' : 'false');
            }

            if (is_float($argument) || is_int($argument)) {
                $expressionArguments[] = new LiteralExpression((string) $argument);
            }

            if (is_string($argument)) {
                $expressionArguments[] = new LiteralExpression(sprintf(
                    '\'%s\'',
                    $this->singleQuotedStringEscaper->escape($argument)
                ));
            }

            if ($argument instanceof ExpressionInterface) {
                $expressionArguments[] = $argument;
            }
        }

        return $expressionArguments;
    }
}
