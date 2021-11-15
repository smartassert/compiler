<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\ErrorOutputFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

trait UnknownElementDataProviderTrait
{
    /**
     * @return array[]
     */
    public function unknownElementDataProvider(): array
    {
        return [
            'test declares step, step contains action with unknown element' => [
                'sourceRelativePath' => '/InvalidTest/action-contains-unknown-element.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ELEMENT,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/action-contains-unknown-element.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unknown element "unknown_element_name"',
                    ErrorOutputFactory::CODE_LOADER_UNKNOWN_ELEMENT,
                    [
                        'element_name' => 'unknown_element_name',
                        'test_path' => FixturePaths::getInvalidTest() . '/action-contains-unknown-element.yml',
                        'step_name' => 'action contains unknown element',
                        'statement' => 'click $elements.unknown_element_name',
                    ]
                ),
            ],
            'test imports step, step contains action with unknown element' => [
                'sourceRelativePath' => '/InvalidTest/import-action-containing-unknown-element.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ELEMENT,
                'expectedCommandOutput' => new ErrorOutput(
                    new Configuration(
                        FixturePaths::getInvalidTest() . '/import-action-containing-unknown-element.yml',
                        FixturePaths::getTarget(),
                        AbstractBaseTest::class
                    ),
                    'Unknown element "unknown_element_name"',
                    ErrorOutputFactory::CODE_LOADER_UNKNOWN_ELEMENT,
                    [
                        'element_name' => 'unknown_element_name',
                        'test_path' => FixturePaths::getInvalidTest() . '/import-action-containing-unknown-element.yml',
                        'step_name' => 'use action_contains_unknown_element',
                        'statement' => 'click $elements.unknown_element_name',
                    ]
                ),
            ],
        ];
    }
}
