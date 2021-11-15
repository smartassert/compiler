<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests;

use PHPUnit\Framework\TestCase;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;

abstract class AbstractEndToEndTest extends TestCase
{
    abstract protected function getRemoteSourcePrefix(): string;

    abstract protected function getRemoteTarget(): string;

    abstract protected function getCompilationOutput(
        CliArguments $cliArguments,
        ?callable $initializer = null
    ): CompilationOutput;
}
