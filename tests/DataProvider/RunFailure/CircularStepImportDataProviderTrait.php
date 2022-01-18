<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\Services\ErrorOutputFactory;

trait CircularStepImportDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function circularStepImportDataProvider(): array
    {
        return [
            'test imports step which imports self' => [
                'sourceRelativePath' => '/InvalidTest/invalid.import-circular-reference-self.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_CIRCULAR_STEP_IMPORT,
                'expectedErrorOutputMessage' => 'Circular step import "circular_reference_self"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_CIRCULAR_STEP_IMPORT,
                'expectedErrorOutputData' => [
                    'import_name' => 'circular_reference_self',
                ],
            ],
            'test imports step which step imports self' => [
                'sourceRelativePath' => '/InvalidTest/invalid.import-circular-reference-indirect.yml',
                'expectedExitCode' => ErrorOutputFactory::CODE_LOADER_CIRCULAR_STEP_IMPORT,
                'expectedErrorOutputMessage' => 'Circular step import "circular_reference_self"',
                'expectedErrorOutputCode' => ErrorOutputFactory::CODE_LOADER_CIRCULAR_STEP_IMPORT,
                'expectedErrorOutputData' => [
                    'import_name' => 'circular_reference_self',
                ],
            ],
        ];
    }
}
