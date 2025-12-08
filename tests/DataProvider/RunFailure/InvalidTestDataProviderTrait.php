<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\ExitCode;

trait InvalidTestDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function invalidTestDataProvider(): array
    {
        return [
            'test has invalid configuration' => [
                'sourceRelativePath' => '/InvalidTest/invalid-configuration.yml',
                'expectedExitCode' => ExitCode::INVALID_TEST->value,
                'expectedErrorOutputMessage' => sprintf(
                    'Invalid test at path "%s": test-configuration-browser-empty',
                    '{{ remoteSourcePrefix }}/InvalidTest/invalid-configuration.yml'
                ),
                'expectedErrorOutputCode' => ExitCode::INVALID_TEST->value,
                'expectedErrorOutputData' => [
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/invalid-configuration.yml',
                    'validation_result' => [
                        'type' => 'test',
                        'reason' => 'test-configuration-browser-empty',
                    ],
                ],
            ],
        ];
    }
}
