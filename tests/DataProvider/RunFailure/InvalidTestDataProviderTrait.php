<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait InvalidTestDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function invalidTestDataProvider(): array
    {
        $testPath = FixturePaths::getInvalidTest() . '/invalid-configuration.yml';
        $testAbsolutePath = '' . $testPath;

        return [
            'test has invalid configuration' => [
                'sourceRelativePath' => '/InvalidTest/invalid-configuration.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_TEST,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        $testAbsolutePath,
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Invalid test at path "' .
                    $testAbsolutePath .
                    '": test-configuration-invalid',
                    ErrorOutputFactory::CODE_LOADER_INVALID_TEST,
                    [
                        'test_path' => $testAbsolutePath,
                        'validation_result' => [
                            'type' => 'test',
                            'reason' => 'test-configuration-invalid',
                            'previous' => [
                                'type' => 'test-configuration',
                                'reason' => 'test-configuration-browser-empty',
                            ],
                        ],
                    ]
                ),
            ],
        ];
    }
}
