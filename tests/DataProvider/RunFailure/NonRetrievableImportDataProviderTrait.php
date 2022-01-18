<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\Services\ErrorOutputFactory;

trait NonRetrievableImportDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function nonRetrievableImportDataProvider(): array
    {
        return [
            'test imports non-parsable page' => [
                'sourceRelativePath' => '/InvalidTest/import-unparseable-page.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_NON_RETRIEVABLE_IMPORT,
                'expectedErrorOutputMessage' => sprintf(
                    'Cannot retrieve page "unparseable_page" from "%s"',
                    '{{ remoteSourcePrefix }}/InvalidPage/unparseable.yml'
                ),
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_NON_RETRIEVABLE_IMPORT,
                'expectedErrorOutputData' => [
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/import-unparseable-page.yml',
                    'type' => 'page',
                    'name' => 'unparseable_page',
                    'import_path' => '{{ remoteSourcePrefix }}/InvalidPage/unparseable.yml',
                    'loader_error' => [
                        'message' => 'Malformed inline YAML string at line 2.',
                        'path' => '{{ remoteSourcePrefix }}/InvalidPage/unparseable.yml',
                    ],
                ],
            ],
        ];
    }
}
