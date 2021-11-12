<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait EmptyTestDataProviderTrait
{
    /**
     * @return array[]
     */
    public function emptyTestDataProvider(): array
    {
        $emptyTestPath = FixturePaths::getInvalidTest() . '/empty.yml';
        $emptyTestAbsolutePath = '' . $emptyTestPath;

        return [
            'test file is empty' => [
                'cliArguments' => [
                    '--source' => $emptyTestPath,
                    '--target' => FixturePaths::getTarget(),
                ],
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_EMPTY_TEST,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        $emptyTestAbsolutePath,
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Empty test at path "' . $emptyTestAbsolutePath . '"',
                    ErrorOutputFactory::CODE_LOADER_EMPTY_TEST,
                    [
                        'path' => $emptyTestAbsolutePath,
                    ]
                ),
            ],
        ];
    }
}
