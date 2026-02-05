<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Resolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Resolver\CircularStepImportException;
use SmartAssert\Compiler\Loader\Resolver\StepImportResolver;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\Statement\Action\ActionCollection;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollection;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilModels\Parser\StepParser;
use webignition\BasilModels\Provider\DataSet\DataSetProvider;
use webignition\BasilModels\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilModels\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilModels\Provider\Step\EmptyStepProvider;
use webignition\BasilModels\Provider\Step\StepProvider;
use webignition\BasilModels\Provider\Step\StepProviderInterface;

class StepImportResolverTest extends TestCase
{
    private StepImportResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = StepImportResolver::createResolver();
    }

    #[DataProvider('resolveStepImportDataProvider')]
    public function testResolveStepImport(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        StepInterface $expectedStep
    ): void {
        $resolvedStep = $this->resolver->resolveStepImport($step, $stepProvider);

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveStepImportDataProvider(): array
    {
        $stepParser = StepParser::create();
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $nonResolvableActions = new ActionCollection([
            $actionParser->parse('wait 1', 0),
        ]);

        $nonResolvableAssertions = new AssertionCollection([
            $assertionParser->parse('$".selector" exists', 0),
        ]);

        $resolvableActions = new ActionCollection([
            $actionParser->parse('click $page_import_name.elements.element_name', 0),
            $actionParser->parse('click $elements.element_name', 0),
            $actionParser->parse(
                'set $page_import_name.elements.element_name to $page_import_name.elements.element_name',
                0,
            ),
            $actionParser->parse(
                'set $elements.element_name to $elements.element_name',
                0,
            ),
        ]);

        $resolvableAssertions = new AssertionCollection([
            $assertionParser->parse('$page_import_name.elements.element_name exists', 0),
            $assertionParser->parse('$elements.element_name exists', 0),
            $assertionParser->parse('$elements.element_name.attribute_name exists', 0),
        ]);

        return [
            'empty step, no imports' => [
                'step' => $stepParser->parse([]),
                'stepProvider' => new EmptyStepProvider(),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([])),
            ],
            'empty step imports empty step' => [
                'step' => $stepParser->parse([
                    'use' => 'step_import_name',
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([]),
                ]),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([])),
            ],
            'empty step imports non-empty step, non-resolvable actions and assertions' => [
                'step' => $stepParser->parse([
                    'use' => 'step_import_name',
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step($nonResolvableActions, $nonResolvableAssertions),
            ],
            'empty step imports non-empty step, resolvable actions and assertions' => [
                'step' => $stepParser->parse([
                    'use' => 'step_import_name',
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step($resolvableActions, $resolvableAssertions),
                ]),
                'expectedStep' => new Step($resolvableActions, $resolvableAssertions),
            ],
            'step with actions and assertions imports step with actions and assertions' => [
                'step' => new Step($resolvableActions, $resolvableAssertions)
                    ->withImportName('step_import_name'),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step(
                    $nonResolvableActions->append($resolvableActions),
                    $nonResolvableAssertions->append($resolvableAssertions),
                ),
            ],
            'deferred' => [
                'step' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('deferred_step_import_name'),
                'stepProvider' => new StepProvider([
                    'deferred_step_import_name' => new Step(new ActionCollection([]), new AssertionCollection([]))
                        ->withImportName('step_import_name'),
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step($nonResolvableActions, $nonResolvableAssertions),
            ],
            'empty step imports actions and assertions, has data provider import name' => [
                'step' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('step_import_name')
                    ->withDataImportName('data_provider_import_name'),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step($nonResolvableActions, $nonResolvableAssertions)
                    ->withDataImportName('data_provider_import_name'),
            ],
        ];
    }

    #[DataProvider('resolveDataProviderImportDataProvider')]
    public function testResolveDataProviderImport(
        StepInterface $step,
        DataSetProviderInterface $dataSetProvider,
        StepInterface $expectedStep
    ): void {
        $resolvedStep = $this->resolver->resolveDataProviderImport(
            $step,
            $dataSetProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveDataProviderImportDataProvider(): array
    {
        return [
            'non-pending step' => [
                'step' => new Step(new ActionCollection([]), new AssertionCollection([])),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([])),
            ],
            'has data provider name, empty step import name' => [
                'step' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withDataImportName('data_provider_import_name'),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        '0' => [
                            'foo' => 'bar',
                        ],
                    ]),
                ]),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withData(
                        new DataSetCollection([
                            '0' => [
                                'foo' => 'bar',
                            ],
                        ])
                    ),
            ],
            'has data provider name, has step import name' => [
                'step' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('step_import_name')
                    ->withDataImportName('data_provider_import_name'),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        '0' => [
                            'foo' => 'bar',
                        ]
                    ]),
                ]),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('step_import_name')
                    ->withData(new DataSetCollection([
                        '0' => [
                            'foo' => 'bar',
                        ]
                    ])),
            ],
        ];
    }

    #[DataProvider('resolveStepImportThrowsCircularReferenceExceptionDataProvider')]
    public function testResolveStepImportThrowsCircularReferenceException(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        string $expectedCircularImportName
    ): void {
        try {
            $this->resolver->resolveStepImport($step, $stepProvider);

            $this->fail('CircularStepImportException not thrown for import "' . $expectedCircularImportName . '"');
        } catch (CircularStepImportException $circularStepImportException) {
            $this->assertSame($expectedCircularImportName, $circularStepImportException->getImportName());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function resolveStepImportThrowsCircularReferenceExceptionDataProvider(): array
    {
        return [
            'direct self-circular reference' => [
                'step' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('start'),
                'stepProvider' => new StepProvider([
                    'start' => new Step(new ActionCollection([]), new AssertionCollection([]))
                        ->withImportName('start'),
                ]),
                'expectedCircularImportName' => 'start',
            ],
            'indirect self-circular reference' => [
                'step' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('start'),
                'stepProvider' => new StepProvider([
                    'start' => new Step(new ActionCollection([]), new AssertionCollection([]))
                        ->withImportName('middle'),
                    'middle' => new Step(new ActionCollection([]), new AssertionCollection([]))
                        ->withImportName('start'),
                ]),
                'expectedCircularImportName' => 'start',
            ],
            'indirect circular reference' => [
                'step' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('one'),
                'stepProvider' => new StepProvider([
                    'one' => new Step(new ActionCollection([]), new AssertionCollection([]))
                        ->withImportName('two'),
                    'two' => new Step(new ActionCollection([]), new AssertionCollection([]))
                        ->withImportName('three'),
                    'three' => new Step(new ActionCollection([]), new AssertionCollection([]))
                        ->withImportName('two'),
                ]),
                'expectedCircularImportName' => 'two',
            ],
        ];
    }
}
