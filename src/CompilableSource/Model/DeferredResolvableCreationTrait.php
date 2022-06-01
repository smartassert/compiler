<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use webignition\StubbleResolvable\ResolvableInterface;

trait DeferredResolvableCreationTrait
{
    private ?ResolvableInterface $resolvable = null;

    public function getTemplate(): string
    {
        return $this->getResolvable()->getTemplate();
    }

    /**
     * @return array<string, ResolvableInterface|string|\Stringable>
     */
    public function getContext(): array
    {
        return $this->getResolvable()->getContext();
    }

    abstract protected function createResolvable(): ResolvableInterface;

    private function getResolvable(): ResolvableInterface
    {
        if (null === $this->resolvable) {
            $this->resolvable = $this->createResolvable();
        }

        return $this->resolvable;
    }
}
