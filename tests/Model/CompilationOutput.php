<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Model;

class CompilationOutput
{
    public function __construct(
        private string $content,
        private int $exitCode,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
