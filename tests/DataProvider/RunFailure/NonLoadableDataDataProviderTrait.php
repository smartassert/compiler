<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\Services\ErrorOutputFactory;

trait NonLoadableDataDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function nonLoadableDataDataProvider(): array
    {
        return [
            'test contains invalid yaml' => [
                'sourceRelativePath' => '/InvalidTest/invalid.unparseable.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_YAML,
                'expectedErrorOutputMessage' => 'Malformed inline YAML string at line 3 (near "- "chrome").',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_INVALID_YAML,
                'expectedErrorOutputData' => [
                    'path' => '{{ remoteSourcePrefix }}/InvalidTest/invalid.unparseable.yml',
                ],
            ],
            'test file contains non-array data' => [
                'sourceRelativePath' => '/InvalidTest/invalid.not-an-array.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_YAML,
                'expectedErrorOutputMessage' => 'Data is not an array',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_INVALID_YAML,
                'expectedErrorOutputData' => [
                    'path' => '{{ remoteSourcePrefix }}/InvalidTest/invalid.not-an-array.yml',
                ],
            ],
        ];
    }
}
