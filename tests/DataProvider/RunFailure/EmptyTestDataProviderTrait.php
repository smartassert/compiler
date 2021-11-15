<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\DataProvider\RunFailure;

use webignition\BasilCliCompiler\Services\ErrorOutputFactory;

trait EmptyTestDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function emptyTestDataProvider(): array
    {
        return [
            'test file is empty' => [
                'sourceRelativePath' => '/InvalidTest/empty.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_EMPTY_TEST,
                'expectedErrorOutputMessage' => 'Empty test at path "{{ remoteSourcePrefix }}/InvalidTest/empty.yml"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_EMPTY_TEST,
                'expectedErrorOutputData' => [
                    'path' => '{{ remoteSourcePrefix }}/InvalidTest/empty.yml',
                ],
            ],
        ];
    }
}
