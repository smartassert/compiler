<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\Services\ErrorOutputFactory;

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
