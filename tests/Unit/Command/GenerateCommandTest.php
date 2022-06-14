<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Command;

use SmartAssert\Compiler\Command\GenerateCommand;
use SmartAssert\Compiler\Services\Compiler;
use SmartAssert\Compiler\Services\ConfigurationFactory;
use SmartAssert\Compiler\Services\ErrorOutputFactory;
use SmartAssert\Compiler\Services\OutputRenderer;
use SmartAssert\Compiler\Services\TestWriter;
use SmartAssert\Compiler\Services\ValidatorInvalidResultSerializer;
use SmartAssert\Compiler\Tests\Unit\AbstractBaseTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilCompilerModels\ErrorOutput;
use webignition\BasilLoader\TestLoader;

class GenerateCommandTest extends AbstractBaseTest
{
    public function testRunFailureInvalidConfiguration(): void
    {
        $input = [];

        $stderr = new BufferedOutput();

        $command = new GenerateCommand(
            TestLoader::createLoader(),
            \Mockery::mock(Compiler::class),
            \Mockery::mock(TestWriter::class),
            new ErrorOutputFactory(new ValidatorInvalidResultSerializer()),
            new OutputRenderer(\Mockery::mock(OutputInterface::class), $stderr),
            new ConfigurationFactory()
        );

        $expectedValidationErrorCode = ErrorOutputFactory::CODE_COMMAND_CONFIG_SOURCE_EMPTY;

        $exitCode = $command->run(new ArrayInput($input), new NullOutput());

        self::assertSame($expectedValidationErrorCode, $exitCode);

        $expectedCommandOutput = new ErrorOutput(
            'source empty; call with --source=SOURCE',
            ErrorOutputFactory::CODE_COMMAND_CONFIG_SOURCE_EMPTY
        );

        $commandOutput = ErrorOutput::fromArray((array) Yaml::parse($stderr->fetch()));
        self::assertEquals($expectedCommandOutput, $commandOutput);
    }
}
