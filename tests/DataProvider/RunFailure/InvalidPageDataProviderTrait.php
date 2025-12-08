<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\ExitCode;

trait InvalidPageDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function invalidPageDataProvider(): array
    {
        return [
            'test imports invalid page; url empty' => [
                'sourceRelativePath' => '/InvalidTest/import-empty-page.yml',
                'expectedExitCode' => ExitCode::INVALID_PAGE->value,
                'expectedErrorOutputMessage' => sprintf(
                    'Invalid page "empty_url_page" at path "%s": page-url-empty',
                    '{{ remoteSourcePrefix }}/InvalidPage/url-empty.yml'
                ),
                'expectedErrorOutputCode' => ExitCode::INVALID_PAGE->value,
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
