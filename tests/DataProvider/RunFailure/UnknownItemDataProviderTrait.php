<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\Services\ErrorOutputFactory;

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
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ITEM,
                'expectedErrorOutputMessage' => 'Unknown dataset "unknown_data_provider_name"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ITEM,
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
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ITEM,
                'expectedErrorOutputMessage' => 'Unknown page "unknown_page_import"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ITEM,
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
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ITEM,
                'expectedErrorOutputMessage' => 'Unknown step "unknown_step"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ITEM,
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
