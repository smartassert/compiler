<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait NonLoadableDataDataProviderTrait
{
    /**
     * @return array[]
     */
    public function nonLoadableDataDataProvider(): array
    {
        return [
            'test contains invalid yaml' => [
                'cliArguments' => [
                    '--source' => FixturePaths::getInvalidTest() . '/invalid.unparseable.yml',
                    '--target' => FixturePaths::getTarget(),
                ],
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_YAML,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/invalid.unparseable.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Malformed inline YAML string at line 3 (near "- "chrome")',
                    ErrorOutputFactory::CODE_LOADER_INVALID_YAML,
                    [
                        'path' => FixturePaths::getInvalidTest() . '/invalid.unparseable.yml',
                    ]
                ),
            ],
            'test file contains non-array data' => [
                'cliArguments' => [
                    '--source' => FixturePaths::getInvalidTest() . '/invalid.not-an-array.yml',
                    '--target' => FixturePaths::getTarget(),
                ],
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_YAML,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/invalid.not-an-array.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Data is not an array',
                    ErrorOutputFactory::CODE_LOADER_INVALID_YAML,
                    [
                        'path' => FixturePaths::getInvalidTest() . '/invalid.not-an-array.yml',
                    ]
                ),
            ],
        ];
    }
}
