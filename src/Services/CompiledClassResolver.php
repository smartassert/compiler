<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\Model\ExternalVariableIdentifiers;
use webignition\Stubble\UnresolvedVariableException;
use webignition\StubbleResolvable\Resolvable;

class CompiledClassResolver
{
    public function __construct(
        private ExternalVariableIdentifiers $externalVariableIdentifiers,
        private VariablePlaceholderResolver $variablePlaceholderResolver
    ) {
    }

    public static function createResolver(ExternalVariableIdentifiers $externalVariableIdentifiers): self
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
