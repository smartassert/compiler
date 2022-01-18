<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use webignition\Stubble\DeciderFactory;
use webignition\Stubble\UnresolvedVariableFinder;
use webignition\Stubble\VariableResolver;

class VariablePlaceholderResolver extends VariableResolver
{
    public function __construct()
    {
        parent::__construct(
            new UnresolvedVariableFinder([
                DeciderFactory::createAllowByPatternDecider('/^\$".*/'),
            ])
        );
    }
}
