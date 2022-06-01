<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use webignition\StubbleResolvable\ResolvableCollectionInterface;
use webignition\StubbleResolvable\ResolvableInterface;

trait DeferredResolvableCollectionTrait
{
    use DeferredResolvableCreationTrait;

    public function count(): int
    {
        $resolvable = $this->getResolvable();

        return $resolvable instanceof ResolvableCollectionInterface
            ? count($resolvable)
            : 1;
    }

    public function getIndexForItem(string|\Stringable|ResolvableInterface $item): ?int
    {
        $resolvable = $this->getResolvable();

        return $resolvable instanceof ResolvableCollectionInterface
            ? $resolvable->getIndexForItem($item)
            : null;
    }
}
