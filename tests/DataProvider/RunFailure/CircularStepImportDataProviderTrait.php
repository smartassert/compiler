<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait CircularStepImportDataProviderTrait
{
    /**
     * @return array[]
     */
    public function circularStepImportDataProvider(): array
    {
        return [
            'test imports step which imports self' => [
                'cliArguments' => [
                    '--source' => FixturePaths::getInvalidTest() . '/invalid.import-circular-reference-self.yml',
                    '--target' => FixturePaths::getTarget(),
                ],
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_CIRCULAR_STEP_IMPORT,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/invalid.import-circular-reference-self.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Circular step import "circular_reference_self"',
                    ErrorOutputFactory::CODE_LOADER_CIRCULAR_STEP_IMPORT,
                    [
                        'import_name' => 'circular_reference_self',
                    ]
                ),
            ],
            'test imports step which step imports self' => [
                'cliArguments' => [
                    '--source' => FixturePaths::getInvalidTest() . '/invalid.import-circular-reference-indirect.yml',
                    '--target' => FixturePaths::getTarget(),
                ],
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_CIRCULAR_STEP_IMPORT,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/invalid.import-circular-reference-indirect.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Circular step import "circular_reference_self"',
                    ErrorOutputFactory::CODE_LOADER_CIRCULAR_STEP_IMPORT,
                    [
                        'import_name' => 'circular_reference_self',
                    ]
                ),
            ],
        ];
    }
}
