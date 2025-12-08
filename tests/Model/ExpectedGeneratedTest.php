<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Model;

class ExpectedGeneratedTest
{
    public function __construct(
        private string $replacementClassName,
        private string $expectedContentPath
    ) {}

    public function getReplacementClassName(): string
    {
        return $this->replacementClassName;
    }

    public function getExpectedContentPath(): string
    {
        return $this->expectedContentPath;
    }

    public function getExpectedContent(): string
    {
        return (string) file_get_contents(getcwd() . $this->expectedContentPath);
    }
}
