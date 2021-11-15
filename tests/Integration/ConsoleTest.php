<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Integration;

use Symfony\Component\Process\Process;
use webignition\BasilCliCompiler\Tests\AbstractEndToEndSuccessTest;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;

class ConsoleTest extends AbstractEndToEndSuccessTest
{
    protected function getRemoteSourcePrefix(): string
    {
        return getcwd() . '/tests/Fixtures/basil';
    }

    protected function getRemoteTarget(): string
    {
        return getcwd() . '/tests/build/target';
    }

    protected function getCompilationOutput(CliArguments $cliArguments): CompilationOutput
    {
        $processArguments = array_merge(['./bin/compiler'], $cliArguments->toArgvArray());
        $process = new Process($processArguments);
        $exitCode = $process->run();

        return new CompilationOutput($process->getOutput(), $exitCode);
    }
}
