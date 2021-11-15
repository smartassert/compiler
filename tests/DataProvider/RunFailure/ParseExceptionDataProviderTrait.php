<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait ParseExceptionDataProviderTrait
{
    /**
     * @return array[]
     */
    public function parseExceptionDataProvider(): array
    {
        $root = getcwd();

        return [
            'test declares step, step contains unparseable action' => [
                'sourceRelativePath' => '/InvalidTest/unparseable-action.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/unparseable-action.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unparseable test',
                    ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                    [
                        'type' => 'test',
                        'test_path' => FixturePaths::getInvalidTest() . '/unparseable-action.yml',
                        'step_name' => 'contains unparseable action',
                        'statement_type' => 'action',
                        'statement' => 'click invalid-identifier',
                        'reason' => 'invalid-identifier',
                    ]
                ),
            ],
            'test declares step, step contains unparseable assertion' => [
                'sourceRelativePath' => '/InvalidTest/unparseable-assertion.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/unparseable-assertion.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unparseable test',
                    ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                    [
                        'type' => 'test',
                        'test_path' => FixturePaths::getInvalidTest() . '/unparseable-assertion.yml',
                        'step_name' => 'contains unparseable assertion',
                        'statement_type' => 'assertion',
                        'statement' => '$page.url is',
                        'reason' => 'empty-value',
                    ]
                ),
            ],
            'test imports step, step contains unparseable action' => [
                'sourceRelativePath' => '/InvalidTest/import-unparseable-action.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/import-unparseable-action.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unparseable step',
                    ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                    [
                        'type' => 'step',
                        'test_path' => FixturePaths::getInvalidTest() . '/import-unparseable-action.yml',
                        'step_path' => $root . '/tests/Fixtures/basil/Step/unparseable-action.yml',
                        'statement_type' => 'action',
                        'statement' => 'click invalid-identifier',
                        'reason' => 'invalid-identifier',
                    ]
                ),
            ],
            'test imports step, step contains unparseable assertion' => [
                'sourceRelativePath' => '/InvalidTest/import-unparseable-assertion.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/import-unparseable-assertion.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unparseable step',
                    ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                    [
                        'type' => 'step',
                        'test_path' => FixturePaths::getInvalidTest() . '/import-unparseable-assertion.yml',
                        'step_path' => $root . '/tests/Fixtures/basil/Step/unparseable-assertion.yml',
                        'statement_type' => 'assertion',
                        'statement' => '$page.url is',
                        'reason' => 'empty-value',
                    ]
                ),
            ],
            'test declares step, step contains non-array actions data' => [
                'sourceRelativePath' => '/InvalidTest/non-array-actions-data.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/non-array-actions-data.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unparseable test',
                    ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                    [
                        'type' => 'test',
                        'test_path' => FixturePaths::getInvalidTest() . '/non-array-actions-data.yml',
                        'step_name' => 'non-array actions data',
                        'reason' => 'invalid-actions-data',
                    ]
                ),
            ],
            'test declares step, step contains non-array assertions data' => [
                'sourceRelativePath' => '/InvalidTest/non-array-assertions-data.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/non-array-assertions-data.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unparseable test',
                    ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                    [
                        'type' => 'test',
                        'test_path' => FixturePaths::getInvalidTest() . '/non-array-assertions-data.yml',
                        'step_name' => 'non-array assertions data',
                        'reason' => 'invalid-assertions-data',
                    ]
                ),
            ],
            'test imports step, step contains non-array actions data' => [
                'sourceRelativePath' => '/InvalidTest/import-non-array-actions-data.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/import-non-array-actions-data.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unparseable step',
                    ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                    [
                        'type' => 'step',
                        'test_path' => FixturePaths::getInvalidTest() . '/import-non-array-actions-data.yml',
                        'step_path' => $root . '/tests/Fixtures/basil/Step/non-array-actions-data.yml',
                        'reason' => 'invalid-actions-data',
                    ]
                ),
            ],
            'test imports step, step contains non-array assertions data' => [
                'sourceRelativePath' => '/InvalidTest/import-non-array-assertions-data.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/import-non-array-assertions-data.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unparseable step',
                    ErrorOutputFactory::CODE_LOADER_UNPARSEABLE_DATA,
                    [
                        'type' => 'step',
                        'test_path' => FixturePaths::getInvalidTest() . '/import-non-array-assertions-data.yml',
                        'step_path' => $root . '/tests/Fixtures/basil/Step/non-array-assertions-data.yml',
                        'reason' => 'invalid-assertions-data',
                    ]
                ),
            ],
        ];
    }
}
