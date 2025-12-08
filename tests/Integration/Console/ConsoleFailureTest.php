<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Integration\Console;

use SmartAssert\Compiler\Tests\AbstractEndToEndFailureTestCase;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use SmartAssert\Compiler\Tests\Model\CompilationOutput;
use Symfony\Component\Process\Process;

class ConsoleFailureTest extends AbstractEndToEndFailureTestCase
{
    protected function getRemoteSourcePrefix(): string
    {
        return getcwd() . '/tests/Fixtures/basil';
    }

    protected function getRemoteTarget(): string
    {
        return getcwd() . '/tests/build/target';
    }

    protected function getCompilationOutput(
        CliArguments $cliArguments,
        ?callable $initializer = null
    ): CompilationOutput {
        $processArguments = array_merge(['./bin/compiler'], $cliArguments->toArgvArray());
        $process = new Process($processArguments);
        $exitCode = $process->run();

        return new CompilationOutput($process->getOutput(), $process->getErrorOutput(), $exitCode);
    }
}
