<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\ExitCode;

trait ParseExceptionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function parseExceptionDataProvider(): array
    {
        return [
            'test declares step, step contains unparseable action' => [
                'sourceRelativePath' => '/InvalidTest/unparseable-action.yml',
                'expectedExitCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputMessage' => 'Unparseable test',
                'expectedErrorOutputCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputData' => [
                    'type' => 'test',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/unparseable-action.yml',
                    'step_name' => 'contains unparseable action',
                    'statement_type' => 'action',
                    'statement' => 'click invalid-identifier',
                    'reason' => 'invalid-identifier',
                ],
            ],
            'test declares step, step contains unparseable assertion' => [
                'sourceRelativePath' => '/InvalidTest/unparseable-assertion.yml',
                'expectedExitCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputMessage' => 'Unparseable test',
                'expectedErrorOutputCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputData' => [
                    'type' => 'test',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/unparseable-assertion.yml',
                    'step_name' => 'contains unparseable assertion',
                    'statement_type' => 'assertion',
                    'statement' => '$page.url is',
                    'reason' => 'empty-value',
                ],
            ],
            'test imports step, step contains unparseable action' => [
                'sourceRelativePath' => '/InvalidTest/import-unparseable-action.yml',
                'expectedExitCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputMessage' => 'Unparseable step',
                'expectedErrorOutputCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputData' => [
                    'type' => 'step',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/import-unparseable-action.yml',
                    'step_path' => '{{ remoteSourcePrefix }}/Step/unparseable-action.yml',
                    'statement_type' => 'action',
                    'statement' => 'click invalid-identifier',
                    'reason' => 'invalid-identifier',
                ],
            ],
            'test imports step, step contains unparseable assertion' => [
                'sourceRelativePath' => '/InvalidTest/import-unparseable-assertion.yml',
                'expectedExitCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputMessage' => 'Unparseable step',
                'expectedErrorOutputCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputData' => [
                    'type' => 'step',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/import-unparseable-assertion.yml',
                    'step_path' => '{{ remoteSourcePrefix }}/Step/unparseable-assertion.yml',
                    'statement_type' => 'assertion',
                    'statement' => '$page.url is',
                    'reason' => 'empty-value',
                ],
            ],
            'test declares step, step contains non-array actions data' => [
                'sourceRelativePath' => '/InvalidTest/non-array-actions-data.yml',
                'expectedExitCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputMessage' => 'Unparseable test',
                'expectedErrorOutputCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputData' => [
                    'type' => 'test',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/non-array-actions-data.yml',
                    'step_name' => 'non-array actions data',
                    'reason' => 'invalid-actions-data',
                ],
            ],
            'test declares step, step contains non-array assertions data' => [
                'sourceRelativePath' => '/InvalidTest/non-array-assertions-data.yml',
                'expectedExitCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputMessage' => 'Unparseable test',
                'expectedErrorOutputCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputData' => [
                    'type' => 'test',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/non-array-assertions-data.yml',
                    'step_name' => 'non-array assertions data',
                    'reason' => 'invalid-assertions-data',
                ],
            ],
            'test imports step, step contains non-array actions data' => [
                'sourceRelativePath' => '/InvalidTest/import-non-array-actions-data.yml',
                'expectedExitCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputMessage' => 'Unparseable step',
                'expectedErrorOutputCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputData' => [
                    'type' => 'step',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/import-non-array-actions-data.yml',
                    'step_path' => '{{ remoteSourcePrefix }}/Step/non-array-actions-data.yml',
                    'reason' => 'invalid-actions-data',
                ],
            ],
            'test imports step, step contains non-array assertions data' => [
                'sourceRelativePath' => '/InvalidTest/import-non-array-assertions-data.yml',
                'expectedExitCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputMessage' => 'Unparseable step',
                'expectedErrorOutputCode' => ExitCode::UNPARSEABLE_DATA->value,
                'expectedErrorOutputData' => [
                    'type' => 'step',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/import-non-array-assertions-data.yml',
                    'step_path' => '{{ remoteSourcePrefix }}/Step/non-array-assertions-data.yml',
                    'reason' => 'invalid-assertions-data',
                ],
            ],
        ];
    }
}
