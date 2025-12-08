<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider\RunFailure;

use SmartAssert\Compiler\ExitCode;

trait CircularStepImportDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function circularStepImportDataProvider(): array
    {
        return [
            'test imports step which imports self' => [
                'sourceRelativePath' => '/InvalidTest/invalid.import-circular-reference-self.yml',
                'expectedExitCode' => ExitCode::CIRCULAR_STEP_IMPORT->value,
                'expectedErrorOutputMessage' => 'Circular step import "circular_reference_self"',
                'expectedErrorOutputCode' => ExitCode::CIRCULAR_STEP_IMPORT->value,
                'expectedErrorOutputData' => [
                    'import_name' => 'circular_reference_self',
                ],
            ],
            'test imports step which step imports self' => [
                'sourceRelativePath' => '/InvalidTest/invalid.import-circular-reference-indirect.yml',
                'expectedExitCode' => ExitCode::CIRCULAR_STEP_IMPORT->value,
                'expectedErrorOutputMessage' => 'Circular step import "circular_reference_self"',
                'expectedErrorOutputCode' => ExitCode::CIRCULAR_STEP_IMPORT->value,
                'expectedErrorOutputData' => [
                    'import_name' => 'circular_reference_self',
                ],
            ],
        ];
    }
}
