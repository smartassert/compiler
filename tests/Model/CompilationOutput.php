<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Model;

class CompilationOutput
{
    public function __construct(
        private readonly string $outputContent,
        private readonly string $errorContent,
        private readonly int $exitCode,
    ) {
    }

    public function getOutputContent(): string
    {
        return $this->outputContent;
    }

    public function getErrorContent(): string
    {
        return $this->errorContent;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
