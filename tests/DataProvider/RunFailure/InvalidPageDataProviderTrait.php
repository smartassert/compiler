<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait InvalidPageDataProviderTrait
{
    /**
     * @return array[]
     */
    public function invalidPageDataProvider(): array
    {
        $root = getcwd();

        $testPath = FixturePaths::getInvalidTest() . '/import-empty-page.yml';
        $testAbsolutePath = '' . $testPath;

        $pagePath = $root . '/tests/Fixtures/basil/InvalidPage/url-empty.yml';
        $pageAbsolutePath = '' . $pagePath;

        return [
            'test imports invalid page; url empty' => [
                'sourceRelativePath' => '/InvalidTest/import-empty-page.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_PAGE,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        $testAbsolutePath,
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Invalid page "empty_url_page" at path "' . $pageAbsolutePath . '": page-url-empty',
                    ErrorOutputFactory::CODE_LOADER_INVALID_PAGE,
                    [
                        'test_path' => $testAbsolutePath,
                        'import_name' => 'empty_url_page',
                        'page_path' => $pageAbsolutePath,
                        'validation_result' => [
                            'type' => 'page',
                            'reason' => 'page-url-empty',
                        ],
                    ]
                ),
            ],
        ];
    }
}
