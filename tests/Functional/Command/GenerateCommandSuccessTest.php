<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Functional\Command;

use SmartAssert\Compiler\Services\CommandFactory;
use SmartAssert\Compiler\Tests\AbstractEndToEndSuccessTest;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use SmartAssert\Compiler\Tests\Model\CompilationOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

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
