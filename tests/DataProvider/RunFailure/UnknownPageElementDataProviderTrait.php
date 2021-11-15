<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait UnknownPageElementDataProviderTrait
{
    /**
     * @return array[]
     */
    public function unknownPageElementDataProvider(): array
    {
        return [
            'test declares step, step contains action using unknown page element' => [
                'sourceRelativePath' => '/InvalidTest/action-contains-unknown-page-element.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/action-contains-unknown-page-element.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unknown page element "unknown_element" in page "page_import_name"',
                    ErrorOutputFactory::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
                    [
                        'import_name' => 'page_import_name',
                        'element_name' => 'unknown_element',
                        'test_path' => FixturePaths::getInvalidTest() . '/action-contains-unknown-page-element.yml',
                        'step_name' => 'action contains unknown page element',
                        'statement' => 'click $page_import_name.elements.unknown_element'
                    ]
                ),
            ],
            'test imports step, test passes step unknown page element' => [
                'sourceRelativePath' => '/InvalidTest/imports-test-passes-unknown-element.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/imports-test-passes-unknown-element.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unknown page element "unknown_element" in page "page_import_name"',
                    ErrorOutputFactory::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
                    [
                        'import_name' => 'page_import_name',
                        'element_name' => 'unknown_element',
                        'test_path' => FixturePaths::getInvalidTest() . '/imports-test-passes-unknown-element.yml',
                        'step_name' => 'action contains unknown page element',
                        'statement' => ''
                    ]
                ),
            ],
        ];
    }
}
