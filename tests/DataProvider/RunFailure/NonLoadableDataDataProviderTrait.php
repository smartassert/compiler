<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\ExitCode;

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
                'expectedExitCode' => ExitCode::INVALID_YAML->value,
                'expectedErrorOutputMessage' => 'Malformed inline YAML string at line 3 (near "- "chrome").',
                'expectedErrorOutputCode' => ExitCode::INVALID_YAML->value,
                'expectedErrorOutputData' => [
                    'path' => '{{ remoteSourcePrefix }}/InvalidTest/invalid.unparseable.yml',
                ],
            ],
            'test file contains non-array data' => [
                'sourceRelativePath' => '/InvalidTest/invalid.not-an-array.yml',
                'expectedExitCode' => ExitCode::INVALID_YAML->value,
                'expectedErrorOutputMessage' => 'Data is not an array',
                'expectedErrorOutputCode' => ExitCode::INVALID_YAML->value,
                'expectedErrorOutputData' => [
                    'path' => '{{ remoteSourcePrefix }}/InvalidTest/invalid.not-an-array.yml',
                ],
            ],
        ];
    }
}
