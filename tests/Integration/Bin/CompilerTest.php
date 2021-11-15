<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Integration\Bin;

use Symfony\Component\Process\Process;
use webignition\BasilCliCompiler\Tests\Integration\AbstractEndToEndTest;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;

class CompilerTest extends AbstractEndToEndTest
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
