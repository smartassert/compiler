<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Command;

use phpmock\mockery\PHPMockery;
use SmartAssert\Compiler\Command\GenerateCommand;
use SmartAssert\Compiler\ExitCode;
use SmartAssert\Compiler\Model\Options;
use SmartAssert\Compiler\Services\Compiler;
use SmartAssert\Compiler\Services\ErrorOutputFactory;
use SmartAssert\Compiler\Services\OutputRenderer;
use SmartAssert\Compiler\Services\TestWriter;
use SmartAssert\Compiler\Services\ValidatorInvalidResultSerializer;
use SmartAssert\Compiler\Tests\Unit\AbstractBaseTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilLoader\TestLoader;

class GenerateCommandTest extends AbstractBaseTestCase
{
    /**
     * @dataProvider runInvalidConfigurationDataProvider
     *
     * @param array<mixed> $input
     * @param array<mixed> $expectedOutputData
     */
    public function testRunInvalidConfiguration(
        array $input,
        callable $initializer,
        int $expectedExitCode,
        array $expectedOutputData,
    ): void {
        $initializer();

        $stderr = new BufferedOutput();

        $command = new GenerateCommand(
            \Mockery::mock(TestLoader::class),
            \Mockery::mock(Compiler::class),
            \Mockery::mock(TestWriter::class),
            new ErrorOutputFactory(new ValidatorInvalidResultSerializer()),
            new OutputRenderer(\Mockery::mock(OutputInterface::class), $stderr)
        );

        $exitCode = $command->run(new ArrayInput($input), new NullOutput());

        self::assertSame($expectedExitCode, $exitCode);

        $output = $stderr->fetch();
        $outputData = Yaml::parse($output);
        self::assertIsArray($outputData);

        self::assertSame($expectedOutputData, $outputData);
    }

    /**
     * @return array<mixed>
     */
    public function runInvalidConfigurationDataProvider(): array
    {
        $mockNamespace = 'SmartAssert\Compiler\Command';
        $isReadableMockArguments = [$mockNamespace, 'is_readable'];
        $isDirMockArguments = [$mockNamespace, 'is_dir'];
        $isWritableMockArguments = [$mockNamespace, 'is_writable'];

        $sourcePath = getcwd() . '/tests/Fixtures/basil/Test/example.com.verify-open-literal.yml';
        $targetPath = getcwd() . '/tests/Fixtures/target';

        return [
            'source empty' => [
                'input' => [
                    '--' . Options::OPTION_SOURCE => '',
                    '--' . Options::OPTION_TARGET => $targetPath,
                    '--' . Options::OPTION_BASE_CLASS => AbstractBaseTestCase::class,
                ],
                'initializer' => function () {},
                'expectedExitCode' => ExitCode::CONFIG_SOURCE_EMPTY->value,
                'expectedErrorData' => [
                    'message' => 'source empty; call with --source=SOURCE',
                    'code' => ExitCode::CONFIG_SOURCE_EMPTY->value,
                ],
            ],
            'source not absolute' => [
                'input' => [
                    '--' . Options::OPTION_SOURCE => 'relative.yaml',
                    '--' . Options::OPTION_TARGET => $targetPath,
                    '--' . Options::OPTION_BASE_CLASS => AbstractBaseTestCase::class,
                ],
                'initializer' => function () {},
                'expectedExitCode' => ExitCode::CONFIG_SOURCE_NOT_ABSOLUTE->value,
                'expectedErrorData' => [
                    'message' => 'source invalid: path must be absolute',
                    'code' => ExitCode::CONFIG_SOURCE_NOT_ABSOLUTE->value,
                ],
            ],
            'source not readable' => [
                'input' => [
                    '--' . Options::OPTION_SOURCE => $sourcePath,
                    '--' . Options::OPTION_TARGET => $targetPath,
                    '--' . Options::OPTION_BASE_CLASS => AbstractBaseTestCase::class,
                ],
                'initializer' => function () use ($isReadableMockArguments, $sourcePath) {
                    PHPMockery::mock(...$isReadableMockArguments)
                        ->with($sourcePath)
                        ->andReturnFalse()
                    ;
                },
                'expectedExitCode' => ExitCode::CONFIG_SOURCE_NOT_READABLE->value,
                'expectedErrorData' => [
                    'message' => 'source invalid; file is not readable',
                    'code' => ExitCode::CONFIG_SOURCE_NOT_READABLE->value,
                ],
            ],
            'target empty' => [
                'input' => [
                    '--' . Options::OPTION_SOURCE => $sourcePath,
                    '--' . Options::OPTION_TARGET => '',
                    '--' . Options::OPTION_BASE_CLASS => AbstractBaseTestCase::class,
                ],
                'initializer' => function () {},
                'expectedExitCode' => ExitCode::CONFIG_TARGET_EMPTY->value,
                'expectedErrorData' => [
                    'message' => 'target empty; call with --target=TARGET',
                    'code' => ExitCode::CONFIG_TARGET_EMPTY->value,
                ],
            ],
            'target not absolute' => [
                'input' => [
                    '--' . Options::OPTION_SOURCE => $sourcePath,
                    '--' . Options::OPTION_TARGET => 'relative',
                    '--' . Options::OPTION_BASE_CLASS => AbstractBaseTestCase::class,
                ],
                'initializer' => function () {},
                'expectedExitCode' => ExitCode::CONFIG_TARGET_NOT_ABSOLUTE->value,
                'expectedErrorData' => [
                    'message' => 'target invalid: path must be absolute',
                    'code' => ExitCode::CONFIG_TARGET_NOT_ABSOLUTE->value,
                ],
            ],
            'target not directory' => [
                'input' => [
                    '--' . Options::OPTION_SOURCE => $sourcePath,
                    '--' . Options::OPTION_TARGET => $targetPath,
                    '--' . Options::OPTION_BASE_CLASS => AbstractBaseTestCase::class,
                ],
                'initializer' => function () use (
                    $isReadableMockArguments,
                    $isDirMockArguments,
                    $sourcePath,
                    $targetPath
                ) {
                    PHPMockery::mock(...$isReadableMockArguments)
                        ->with($sourcePath)
                        ->andReturnTrue()
                    ;

                    PHPMockery::mock(...$isDirMockArguments)
                        ->with($targetPath)
                        ->andReturnFalse()
                    ;
                },
                'expectedExitCode' => ExitCode::CONFIG_TARGET_NOT_A_DIRECTORY->value,
                'expectedErrorData' => [
                    'message' => 'target invalid; is not a directory (is it a file?)',
                    'code' => ExitCode::CONFIG_TARGET_NOT_A_DIRECTORY->value,
                ],
            ],
            'target not writable' => [
                'input' => [
                    '--' . Options::OPTION_SOURCE => $sourcePath,
                    '--' . Options::OPTION_TARGET => $targetPath,
                    '--' . Options::OPTION_BASE_CLASS => AbstractBaseTestCase::class,
                ],
                'initializer' => function () use (
                    $isReadableMockArguments,
                    $isDirMockArguments,
                    $isWritableMockArguments,
                    $sourcePath,
                    $targetPath
                ) {
                    PHPMockery::mock(...$isReadableMockArguments)
                        ->with($sourcePath)
                        ->andReturnTrue()
                    ;

                    PHPMockery::mock(...$isDirMockArguments)
                        ->with($targetPath)
                        ->andReturnTrue()
                    ;

                    PHPMockery::mock(...$isWritableMockArguments)
                        ->with($targetPath)
                        ->andReturnFalse()
                    ;
                },
                'expectedExitCode' => ExitCode::CONFIG_TARGET_NOT_WRITABLE->value,
                'expectedErrorData' => [
                    'message' => 'target invalid; directory is not writable',
                    'code' => ExitCode::CONFIG_TARGET_NOT_WRITABLE->value,
                ],
            ],
        ];
    }
}
