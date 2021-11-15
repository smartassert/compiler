<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait NonRetrievableImportDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function nonRetrievableImportDataProvider(): array
    {
        $root = getcwd();

        $pagePath = $root . '/tests/Fixtures/basil/InvalidPage/unparseable.yml';
        $pageAbsolutePath = '' . $pagePath;

        $testPath = FixturePaths::getInvalidTest() . '/import-unparseable-page.yml';
        $testAbsolutePath = '' . $testPath;

        return [
            'test imports non-parsable page' => [
                'sourceRelativePath' => '/InvalidTest/import-unparseable-page.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_NON_RETRIEVABLE_IMPORT,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        $testAbsolutePath,
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Cannot retrieve page "unparseable_page" from "' . $pageAbsolutePath . '"',
                    ErrorOutputFactory::CODE_LOADER_NON_RETRIEVABLE_IMPORT,
                    [
                        'test_path' => $testAbsolutePath,
                        'type' => 'page',
                        'name' => 'unparseable_page',
                        'import_path' => $pageAbsolutePath,
                        'loader_error' => [
                            'message' => 'Malformed inline YAML string at line 2',
                            'path' => $pageAbsolutePath,
                        ],
                    ]
                ),
            ],
        ];
    }
}
