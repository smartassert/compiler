<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BasilCliCompiler\Services\ErrorOutputFactory;

trait UnknownPageElementDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function unknownPageElementDataProvider(): array
    {
        return [
            'test declares step, step contains action using unknown page element' => [
                'sourceRelativePath' => '/InvalidTest/action-contains-unknown-page-element.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
                'expectedErrorOutputMessage' => 'Unknown page element "unknown_element" in page "page_import_name"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
                'expectedErrorOutputData' => [
                    'import_name' => 'page_import_name',
                    'element_name' => 'unknown_element',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/action-contains-unknown-page-element.yml',
                    'step_name' => 'action contains unknown page element',
                    'statement' => 'click $page_import_name.elements.unknown_element'
                ],
            ],
            'test imports step, test passes step unknown page element' => [
                'sourceRelativePath' => '/InvalidTest/imports-test-passes-unknown-element.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
                'expectedErrorOutputMessage' => 'Unknown page element "unknown_element" in page "page_import_name"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
                'expectedErrorOutputData' => [
                    'import_name' => 'page_import_name',
                    'element_name' => 'unknown_element',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/imports-test-passes-unknown-element.yml',
                    'step_name' => 'action contains unknown page element',
                    'statement' => ''
                ],
            ],
        ];
    }
}
