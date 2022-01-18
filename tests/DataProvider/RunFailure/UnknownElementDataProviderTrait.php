<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\Services\ErrorOutputFactory;

trait UnknownElementDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function unknownElementDataProvider(): array
    {
        return [
            'test declares step, step contains action with unknown element' => [
                'sourceRelativePath' => '/InvalidTest/action-contains-unknown-element.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ELEMENT,
                'expectedErrorOutputMessage' => 'Unknown element "unknown_element_name"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ELEMENT,
                'expectedErrorOutputData' => [
                    'element_name' => 'unknown_element_name',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/action-contains-unknown-element.yml',
                    'step_name' => 'action contains unknown element',
                    'statement' => 'click $elements.unknown_element_name',
                ],
            ],
            'test imports step, step contains action with unknown element' => [
                'sourceRelativePath' => '/InvalidTest/import-action-containing-unknown-element.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ELEMENT,
                'expectedErrorOutputMessage' => 'Unknown element "unknown_element_name"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_UNKNOWN_ELEMENT,
                'expectedErrorOutputData' => [
                    'element_name' => 'unknown_element_name',
                    'test_path' => '{{ remoteSourcePrefix }}/InvalidTest/import-action-containing-unknown-element.yml',
                    'step_name' => 'use action_contains_unknown_element',
                    'statement' => 'click $elements.unknown_element_name',
                ],
            ],
        ];
    }
}
