<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvableWithoutContext;

trait ResolvableStringableTrait
{
    use DeferredResolvableCreationTrait;

    protected function createResolvable(): ResolvableInterface
    {
        return new ResolvableWithoutContext((string) $this);
    }
}
