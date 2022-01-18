<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\Services\ErrorOutputFactory;

trait InvalidPageDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function invalidPageDataProvider(): array
    {
        return [
            'test imports invalid page; url empty' => [
                'sourceRelativePath' => '/InvalidTest/import-empty-page.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_PAGE,
                'expectedErrorOutputMessage' => sprintf(
                    'Invalid page "empty_url_page" at path "%s": page-url-empty',
                    '{{ remoteSourcePrefix }}/InvalidPage/url-empty.yml'
                ),
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_INVALID_PAGE,
                'expectedErrorOutputData' => [
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/import-empty-page.yml',
                    'import_name' => 'empty_url_page',
                    'page_path' => '{{ remoteSourcePrefix }}/InvalidPage/url-empty.yml',
                    'validation_result' => [
                        'type' => 'page',
                        'reason' => 'page-url-empty',
                    ],
                ],
            ],
        ];
    }
}
