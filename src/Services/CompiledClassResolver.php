<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\Model\DependencyVariables;
use webignition\Stubble\Resolvable\Resolvable;
use webignition\Stubble\UnresolvedVariableException;

class CompiledClassResolver
{
    public function __construct(
        private DependencyVariables $externalVariableIdentifiers,
        private VariablePlaceholderResolver $variablePlaceholderResolver
    ) {}

    public static function createResolver(DependencyVariables $externalVariableIdentifiers): self
    {
        return new CompiledClassResolver(
            $externalVariableIdentifiers,
            new VariablePlaceholderResolver()
        );
    }

    /**
     * @throws UnresolvedVariableException
     */
    public function resolve(string $compiledClass): string
    {
        $compiledClassLines = explode("\n", $compiledClass);

        $resolvedLines = [];

        foreach ($compiledClassLines as $line) {
            $resolvedLines[] = $this->variablePlaceholderResolver->resolve(
                new Resolvable(
                    $line,
                    $this->externalVariableIdentifiers->get()
                )
            );
        }

        return implode("\n", $resolvedLines);
    }
}
