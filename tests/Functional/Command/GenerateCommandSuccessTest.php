<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Functional\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use webignition\BasilCliCompiler\Services\CommandFactory;
use webignition\BasilCliCompiler\Tests\AbstractEndToEndSuccessTest;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;

class GenerateCommandSuccessTest extends AbstractEndToEndSuccessTest
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
        $stdout = new BufferedOutput();
        $command = CommandFactory::createGenerateCommand($stdout, new NullOutput(), $cliArguments->toArgvArray());

        $exitCode = $command->run(new ArrayInput($cliArguments->getOptions()), new NullOutput());

        return new CompilationOutput($stdout->fetch(), $exitCode);
    }
}
