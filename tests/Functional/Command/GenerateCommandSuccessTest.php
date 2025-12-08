<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Functional\Command;

use SmartAssert\Compiler\Services\CommandFactory;
use SmartAssert\Compiler\Tests\AbstractEndToEndSuccessTestCase;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use SmartAssert\Compiler\Tests\Model\CompilationOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class GenerateCommandSuccessTest extends AbstractEndToEndSuccessTestCase
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
        $stderr = new BufferedOutput();
        $command = CommandFactory::createGenerateCommand($stdout, $stderr, $cliArguments->toArgvArray());

        $exitCode = $command->run(new ArrayInput($cliArguments->getOptions()), $stderr);

        return new CompilationOutput($stdout->fetch(), $stderr->fetch(), $exitCode);
    }
}
