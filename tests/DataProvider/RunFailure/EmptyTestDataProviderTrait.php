<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\ExitCode;

trait EmptyTestDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function emptyTestDataProvider(): array
    {
        return [
            'test file is empty' => [
                'sourceRelativePath' => '/InvalidTest/empty.yml',
                'expectedExitCode' => ExitCode::EMPTY_TEST->value,
                'expectedErrorOutputMessage' => 'Empty test at path "{{ remoteSourcePrefix }}/InvalidTest/empty.yml"',
                'expectedErrorOutputCode' => ExitCode::EMPTY_TEST->value,
                'expectedErrorOutputData' => [
                    'path' => '{{ remoteSourcePrefix }}/InvalidTest/empty.yml',
                ],
            ],
        ];
    }
}
