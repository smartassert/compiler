<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait InvalidTestDataProviderTrait
{
    /**
     * @return array[]
     */
    public function invalidTestDataProvider(): array
    {
        $root = getcwd();

        $testPath = $root . '/tests/Fixtures/basil/InvalidTest/invalid-configuration.yml';
        $testAbsolutePath = '' . $testPath;

        return [
            'test has invalid configuration' => [
                'input' => [
                    '--source' => $testPath,
                    '--target' => $root . '/tests/build/target',
                ],
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_TEST,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        $testAbsolutePath,
                        $root . '/tests/build/target',
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
