<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\ExitCode;

trait UnknownItemDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function unknownItemDataProvider(): array
    {
        return [
            'test declares step, step uses unknown dataset' => [
                'sourceRelativePath' => '/InvalidTest/step-uses-unknown-dataset.yml',
                'expectedExitCode' => ExitCode::UNKNOWN_ITEM->value,
                'expectedErrorOutputMessage' => 'Unknown dataset "unknown_data_provider_name"',
                'expectedErrorOutputCode' => ExitCode::UNKNOWN_ITEM->value,
                'expectedErrorOutputData' => [
                    'type' => 'dataset',
                    'name' => 'unknown_data_provider_name',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/step-uses-unknown-dataset.yml',
                    'step_name' => 'step name',
                    'statement' => '',
                ],
            ],
            'test declares step, step uses unknown page' => [
                'sourceRelativePath' => '/InvalidTest/step-uses-unknown-page.yml',
                'expectedExitCode' => ExitCode::UNKNOWN_ITEM->value,
                'expectedErrorOutputMessage' => 'Unknown page "unknown_page_import"',
                'expectedErrorOutputCode' => ExitCode::UNKNOWN_ITEM->value,
                'expectedErrorOutputData' => [
                    'type' => 'page',
                    'name' => 'unknown_page_import',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/step-uses-unknown-page.yml',
                    'step_name' => 'step name',
                    'statement' => '',
                ],
            ],
            'test declares step, step uses step' => [
                'sourceRelativePath' => '/InvalidTest/step-uses-unknown-step.yml',
                'expectedExitCode' => ExitCode::UNKNOWN_ITEM->value,
                'expectedErrorOutputMessage' => 'Unknown step "unknown_step"',
                'expectedErrorOutputCode' => ExitCode::UNKNOWN_ITEM->value,
                'expectedErrorOutputData' => [
                    'type' => 'step',
                    'name' => 'unknown_step',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/step-uses-unknown-step.yml',
                    'step_name' => 'step name',
                    'statement' => '',
                ],
            ],
        ];
    }
}
