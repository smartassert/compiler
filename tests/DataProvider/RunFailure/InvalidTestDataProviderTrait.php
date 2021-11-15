<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BasilCliCompiler\Services\ErrorOutputFactory;

trait InvalidTestDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function invalidTestDataProvider(): array
    {
        return [
            'test has invalid configuration' => [
                'sourceRelativePath' => '/InvalidTest/invalid-configuration.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_INVALID_TEST,
                'expectedErrorOutputMessage' => sprintf(
                    'Invalid test at path "%s": test-configuration-invalid',
                    '{{ remoteSourcePrefix }}/InvalidTest/invalid-configuration.yml'
                ),
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_INVALID_TEST,
                'expectedErrorOutputData' => [
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/invalid-configuration.yml',
                    'validation_result' => [
                        'type' => 'test',
                        'reason' => 'test-configuration-invalid',
                        'previous' => [
                            'type' => 'test-configuration',
                            'reason' => 'test-configuration-browser-empty',
                        ],
                    ],
                ],
            ],
        ];
    }
}
