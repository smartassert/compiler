<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use SmartAssert\Compiler\Services\ErrorOutputFactory;
use SmartAssert\Compiler\Services\ValidatorInvalidResultSerializer;
use SmartAssert\Compiler\Tests\Unit\AbstractBaseTest;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

class ErrorOutputFactoryTest extends AbstractBaseTest
{
    private ErrorOutputFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ErrorOutputFactory(
            new ValidatorInvalidResultSerializer()
        );
    }

    /**
     * @dataProvider createFromInvalidConfigurationDataProvider
     */
    public function testCreateFromInvalidConfiguration(
        int $configurationValidationState,
        ErrorOutput $expectedOutput
    ): void {
        self::assertEquals(
            $expectedOutput,
            $this->factory->createFromInvalidConfiguration(
                $configurationValidationState
            )
        );
    }

    /**
     * @return array<mixed>
     */
    public function createFromInvalidConfigurationDataProvider(): array
    {
        return [
            'source not readable' => [
                'configurationValidationState' => Configuration::VALIDATION_STATE_SOURCE_NOT_READABLE,
                'expectedOutput' => new ErrorOutput(
                    ErrorOutputFactory::MESSAGE_COMMAND_CONFIG_SOURCE_NOT_READABLE,
                    ErrorOutputFactory::CODE_COMMAND_CONFIG_SOURCE_NOT_READABLE
                ),
            ],
            'target not writable' => [
                'configurationValidationState' => Configuration::VALIDATION_STATE_TARGET_NOT_WRITABLE,
                'expectedOutput' => new ErrorOutput(
                    ErrorOutputFactory::MESSAGE_COMMAND_CONFIG_TARGET_NOT_WRITABLE,
                    ErrorOutputFactory::CODE_COMMAND_CONFIG_TARGET_NOT_WRITABLE
                ),
            ],
            'target not a directory' => [
                'configurationValidationState' => Configuration::VALIDATION_STATE_TARGET_NOT_DIRECTORY,
                'expectedOutput' => new ErrorOutput(
                    ErrorOutputFactory::MESSAGE_COMMAND_CONFIG_TARGET_NOT_A_DIRECTORY,
                    ErrorOutputFactory::CODE_COMMAND_CONFIG_TARGET_NOT_A_DIRECTORY
                ),
            ],
            'source not absolute' => [
                'configurationValidationState' => Configuration::VALIDATION_STATE_SOURCE_NOT_ABSOLUTE,
                'expectedOutput' => new ErrorOutput(
                    ErrorOutputFactory::MESSAGE_COMMAND_CONFIG_SOURCE_NOT_ABSOLUTE,
                    ErrorOutputFactory::CODE_COMMAND_CONFIG_SOURCE_NOT_ABSOLUTE
                ),
            ],
            'target not absolute' => [
                'configurationValidationState' => Configuration::VALIDATION_STATE_TARGET_NOT_ABSOLUTE,
                'expectedOutput' => new ErrorOutput(
                    ErrorOutputFactory::MESSAGE_COMMAND_CONFIG_TARGET_NOT_ABSOLUTE,
                    ErrorOutputFactory::CODE_COMMAND_CONFIG_TARGET_NOT_ABSOLUTE
                ),
            ],
            'source empty' => [
                'configurationValidationState' => Configuration::VALIDATION_STATE_SOURCE_EMPTY,
                'expectedOutput' => new ErrorOutput(
                    ErrorOutputFactory::MESSAGE_COMMAND_CONFIG_SOURCE_EMPTY,
                    ErrorOutputFactory::CODE_COMMAND_CONFIG_SOURCE_EMPTY
                ),
            ],
            'target empty' => [
                'configurationValidationState' => Configuration::VALIDATION_STATE_TARGET_EMPTY,
                'expectedOutput' => new ErrorOutput(
                    ErrorOutputFactory::MESSAGE_COMMAND_CONFIG_TARGET_EMPTY,
                    ErrorOutputFactory::CODE_COMMAND_CONFIG_TARGET_EMPTY
                ),
            ],
        ];
    }
}
