<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use webignition\BasilCliCompiler\Model\Configuration;
use webignition\BasilCliCompiler\Model\ErrorOutput;
use webignition\BasilCliCompiler\Services\ConfigurationValidator;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Services\ValidatorInvalidResultSerializer;
use webignition\BasilCliCompiler\Tests\Services\ProjectRootPathProvider;
use webignition\BasilCliCompiler\Tests\Unit\AbstractBaseTest;

class ErrorOutputFactoryTest extends AbstractBaseTest
{
    /**
     * @dataProvider createFromInvalidConfigurationDataProvider
     */
    public function testCreateFromInvalidConfiguration(
        Configuration $configuration,
        ConfigurationValidator $generateCommandConfigurationValidator,
        ErrorOutput $expectedOutput
    ) {
        $factory = new ErrorOutputFactory(
            $generateCommandConfigurationValidator,
            new ValidatorInvalidResultSerializer()
        );

        self::assertEquals($expectedOutput, $factory->createFromInvalidConfiguration($configuration));
    }

    public function createFromInvalidConfigurationDataProvider(): array
    {
        $root = (new ProjectRootPathProvider())->get();

        $source = $root . '/tests/Fixtures/basil/Test/example.com.verify-open-literal.yml';
        $target = $root . '/tests/build/target';
        $baseClass = TestCase::class;

        return [
            'source does not exist' => [
                'configuration' => new Configuration('', $target, $baseClass),
                'generateCommandConfigurationValidator' => $this->createConfigurationValidator(
                    new Configuration('', $target, $baseClass),
                    ErrorOutput::CODE_COMMAND_CONFIG_SOURCE_INVALID_DOES_NOT_EXIST
                ),
                'expectedOutput' => new ErrorOutput(
                    new Configuration('', $target, $baseClass),
                    'source invalid; does not exist',
                    ErrorOutput::CODE_COMMAND_CONFIG_SOURCE_INVALID_DOES_NOT_EXIST
                ),
            ],
            'source not readable' => [
                'configuration' => new Configuration('', $target, $baseClass),
                'generateCommandConfigurationValidator' => $this->createConfigurationValidator(
                    new Configuration('', $target, $baseClass),
                    ErrorOutput::CODE_COMMAND_CONFIG_SOURCE_INVALID_NOT_READABLE
                ),
                'expectedOutput' => new ErrorOutput(
                    new Configuration('', $target, $baseClass),
                    'source invalid; file is not readable',
                    ErrorOutput::CODE_COMMAND_CONFIG_SOURCE_INVALID_NOT_READABLE
                ),
            ],
            'target does not exist' => [
                'configuration' => new Configuration($source, '', $baseClass),
                'generateCommandConfigurationValidator' => $this->createConfigurationValidator(
                    new Configuration($source, '', $baseClass),
                    ErrorOutput::CODE_COMMAND_CONFIG_TARGET_INVALID_DOES_NOT_EXIST
                ),
                'expectedOutput' => new ErrorOutput(
                    new Configuration($source, '', $baseClass),
                    'target invalid; does not exist',
                    ErrorOutput::CODE_COMMAND_CONFIG_TARGET_INVALID_DOES_NOT_EXIST
                ),
            ],
            'target not writable' => [
                'configuration' => new Configuration($source, '', $baseClass),
                'generateCommandConfigurationValidator' => $this->createConfigurationValidator(
                    new Configuration($source, '', $baseClass),
                    ErrorOutput::CODE_COMMAND_CONFIG_TARGET_INVALID_NOT_WRITABLE
                ),
                'expectedOutput' => new ErrorOutput(
                    new Configuration($source, '', $baseClass),
                    'target invalid; directory is not writable',
                    ErrorOutput::CODE_COMMAND_CONFIG_TARGET_INVALID_NOT_WRITABLE
                ),
            ],
            'target not a directory' => [
                'configuration' => new Configuration($source, $source, $baseClass),
                'generateCommandConfigurationValidator' => $this->createConfigurationValidator(
                    new Configuration($source, $source, $baseClass),
                    ErrorOutput::CODE_COMMAND_CONFIG_TARGET_INVALID_NOT_A_DIRECTORY
                ),
                'expectedOutput' => new ErrorOutput(
                    new Configuration($source, $source, $baseClass),
                    'target invalid; is not a directory (is it a file?)',
                    ErrorOutput::CODE_COMMAND_CONFIG_TARGET_INVALID_NOT_A_DIRECTORY
                ),
            ],
        ];
    }

    private function createConfigurationValidator(
        Configuration $expectedConfiguration,
        int $errorCode
    ): ConfigurationValidator {
        $validator = \Mockery::mock(ConfigurationValidator::class);

        $validator
            ->shouldReceive('deriveInvalidConfigurationErrorCode')
            ->withArgs(function (Configuration $configuration) use ($expectedConfiguration) {
                self::assertEquals($expectedConfiguration, $configuration);

                return true;
            })
            ->andReturn($errorCode);

        return $validator;
    }
}