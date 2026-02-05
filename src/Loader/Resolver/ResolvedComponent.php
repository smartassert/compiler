<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver;

class ResolvedComponent
{
    public function __construct(
        private ?string $source,
        private ?string $resolved
    ) {}

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getResolved(): ?string
    {
        return $this->resolved;
    }

    public function isResolved(): bool
    {
        return $this->resolved !== $this->source;
    }
}
