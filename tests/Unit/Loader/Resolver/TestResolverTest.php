<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Resolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Resolver\TestResolver;
use SmartAssert\Compiler\Loader\Resolver\UnknownElementException;
use SmartAssert\Compiler\Loader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Model\Statement\Action\Action;
use webignition\BasilModels\Model\Statement\Action\ActionCollection;
use webignition\BasilModels\Model\Statement\Action\ResolvedAction;
use webignition\BasilModels\Model\Statement\Assertion\Assertion;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollection;
use webignition\BasilModels\Model\Statement\Assertion\ResolvedAssertion;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepCollection;
use webignition\BasilModels\Model\Test\Test;
use webignition\BasilModels\Model\Test\TestInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilModels\Parser\StepParser;
use webignition\BasilModels\Parser\Test\TestParser;
use webignition\BasilModels\Provider\DataSet\DataSetProvider;
use webignition\BasilModels\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilModels\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Page\EmptyPageProvider;
use webignition\BasilModels\Provider\Page\PageProvider;
use webignition\BasilModels\Provider\Page\PageProviderInterface;
use webignition\BasilModels\Provider\Step\EmptyStepProvider;
use webignition\BasilModels\Provider\Step\StepProvider;
use webignition\BasilModels\Provider\Step\StepProviderInterface;

class TestResolverTest extends TestCase
{
    private TestResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = TestResolver::createResolver();
    }

    #[DataProvider('resolveSuccessDataProvider')]
    public function testResolveSuccess(
        TestInterface $test,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        TestInterface $expectedTest
    ): void {
        $resolvedTest = $this->resolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);

        $this->assertEquals($expectedTest, $resolvedTest);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveSuccessDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $expectedResolvedDataTest = new Test('chrome', 'http://example.com/', new StepCollection([
            'step name' => new Step(
                new ActionCollection([
                    new Action(
                        'set $".action-selector" to $data.key1',
                        0,
                        'set',
                        '$".action-selector" to $data.key1',
                        '$".action-selector"',
                        '$data.key1'
                    ),
                ]),
                new AssertionCollection([
                    new Assertion(
                        '$".assertion-selector" is $data.key2',
                        1,
                        '$".assertion-selector"',
                        'is',
                        '$data.key2'
                    ),
                ])
            )->withData(new DataSetCollection([
                '0' => [
                    'key1' => 'key1value1',
                    'key2' => 'key2value1',
                ],
                '1' => [
                    'key1' => 'key1value2',
                    'key2' => 'key2value2',
                ],
            ])),
        ]));

        $testParser = TestParser::create();
        $stepParser = StepParser::create();

        return [
            'literal url is unchanged' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([])),
            ],
            'page import url reference is resolved' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => '$page_import_name.url',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com/'),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([])),
            ],
            'empty step' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([
                    'step name' => new Step(new ActionCollection([]), new AssertionCollection([])),
                ])),
            ],
            'no imports, actions and assertions require no resolution' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'actions' => [
                            'click $".action-selector"',
                        ],
                        'assertions' => [
                            '$".assertion-selector" exists',
                        ],
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([
                    'step name' => new Step(
                        new ActionCollection([
                            new Action(
                                'click $".action-selector"',
                                0,
                                'click',
                                '$".action-selector"',
                                '$".action-selector"'
                            ),
                        ]),
                        new AssertionCollection([
                            new Assertion(
                                '$".assertion-selector" exists',
                                1,
                                '$".assertion-selector"',
                                'exists'
                            ),
                        ])
                    ),
                ])),
            ],
            'actions and assertions require resolution of page imports' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'actions' => [
                            'click $page_import_name.elements.action_selector',
                        ],
                        'assertions' => [
                            '$page_import_name.elements.assertion_selector exists',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'action_selector' => '$".action-selector"',
                            'assertion_selector' => '$".assertion-selector"',
                        ]
                    ),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([
                    'step name' => new Step(
                        new ActionCollection([
                            new ResolvedAction(
                                $actionParser->parse('click $page_import_name.elements.action_selector', 0),
                                '$".action-selector"'
                            ),
                        ]),
                        new AssertionCollection([
                            new ResolvedAssertion(
                                $assertionParser->parse('$page_import_name.elements.assertion_selector exists', 1),
                                '$".assertion-selector"'
                            ),
                        ])
                    ),
                ])),
            ],
            'empty step imports step, imported actions and assertions require no resolution' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'use' => 'step_import_name',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'actions' => [
                            'click $".action-selector"',
                        ],
                        'assertions' => [
                            '$".assertion-selector" exists',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([
                    'step name' => new Step(
                        new ActionCollection([
                            new Action(
                                'click $".action-selector"',
                                0,
                                'click',
                                '$".action-selector"',
                                '$".action-selector"'
                            ),
                        ]),
                        new AssertionCollection([
                            new Assertion(
                                '$".assertion-selector" exists',
                                1,
                                '$".assertion-selector"',
                                'exists'
                            ),
                        ])
                    ),
                ])),
            ],
            'empty step imports step, imported actions and assertions require element resolution' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'use' => 'step_import_name',
                        'elements' => [
                            'elements_action_selector' => '$page_import_name.elements.page_action_selector',
                            'elements_assertion_selector' => '$page_import_name.elements.page_assertion_selector',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'page_action_selector' => '$".action-selector"',
                            'page_assertion_selector' => '$".assertion-selector"',
                        ]
                    ),
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'actions' => [
                            'click $elements.elements_action_selector',
                        ],
                        'assertions' => [
                            '$elements.elements_assertion_selector exists',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([
                    'step name' => new Step(
                        new ActionCollection([
                            new ResolvedAction(
                                $actionParser->parse('click $elements.elements_action_selector', 0),
                                '$".action-selector"'
                            ),
                        ]),
                        new AssertionCollection([
                            new ResolvedAssertion(
                                $assertionParser->parse('$elements.elements_assertion_selector exists', 1),
                                '$".assertion-selector"'
                            ),
                        ])
                    ),
                ])),
            ],
            'empty step imports step, imported actions and assertions use inline data' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'use' => 'step_import_name',
                        'data' => [
                            '0' => [
                                'key1' => 'key1value1',
                                'key2' => 'key2value1',
                            ],
                            '1' => [
                                'key1' => 'key1value2',
                                'key2' => 'key2value2',
                            ],
                        ],
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'actions' => [
                            'set $".action-selector" to $data.key1',
                        ],
                        'assertions' => [
                            '$".assertion-selector" is $data.key2',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => $expectedResolvedDataTest,
            ],
            'empty step imports step, imported actions and assertions use imported data' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'use' => 'step_import_name',
                        'data' => 'data_provider_import_name',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'actions' => [
                            'set $".action-selector" to $data.key1',
                        ],
                        'assertions' => [
                            '$".assertion-selector" is $data.key2',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        '0' => [
                            'key1' => 'key1value1',
                            'key2' => 'key2value1',
                        ],
                        '1' => [
                            'key1' => 'key1value2',
                            'key2' => 'key2value2',
                        ],
                    ]),
                ]),
                'expectedTest' => $expectedResolvedDataTest,
            ],
            'deferred step import, imported actions and assertions require element resolution' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'use' => 'step_import_name',
                        'elements' => [
                            'action_selector' => '$page_import_name.elements.action_selector',
                            'assertion_selector' => '$page_import_name.elements.assertion_selector',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'action_selector' => '$".action-selector"',
                            'assertion_selector' => '$".assertion-selector"',
                        ]
                    ),
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'use' => 'deferred',
                    ]),
                    'deferred' => $stepParser->parse([
                        'actions' => [
                            'click $elements.action_selector',
                        ],
                        'assertions' => [
                            '$elements.assertion_selector exists',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([
                    'step name' => new Step(
                        new ActionCollection([
                            new ResolvedAction(
                                $actionParser->parse('click $elements.action_selector', 0),
                                '$".action-selector"'
                            ),
                        ]),
                        new AssertionCollection([
                            new ResolvedAssertion(
                                $assertionParser->parse('$elements.assertion_selector exists', 1),
                                '$".assertion-selector"'
                            ),
                        ])
                    ),
                ])),
            ],
            'deferred step import, imported actions and assertions use imported data' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'use' => 'step_import_name',
                        'data' => 'data_provider_import_name',
                        'elements' => [
                            'action_selector' => '$page_import_name.elements.action_selector',
                            'assertion_selector' => '$page_import_name.elements.assertion_selector',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'action_selector' => '$".action-selector"',
                            'assertion_selector' => '$".assertion-selector"',
                        ]
                    ),
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'use' => 'deferred',
                    ]),
                    'deferred' => $stepParser->parse([
                        'actions' => [
                            'set $elements.action_selector to $data.key1',
                        ],
                        'assertions' => [
                            '$elements.assertion_selector is $data.key2',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        '0' => [
                            'key1' => 'key1value1',
                            'key2' => 'key2value1',
                        ],
                        '1' => [
                            'key1' => 'key1value2',
                            'key2' => 'key2value2',
                        ],
                    ]),
                ]),
                'expectedTest' => new Test('chrome', 'http://example.com/', new StepCollection([
                    'step name' => new Step(
                        new ActionCollection([
                            new ResolvedAction(
                                $actionParser->parse('set $elements.action_selector to $data.key1', 0),
                                '$".action-selector"',
                                '$data.key1'
                            ),
                        ]),
                        new AssertionCollection([
                            new ResolvedAssertion(
                                $assertionParser->parse('$elements.assertion_selector is $data.key2', 1),
                                '$".assertion-selector"',
                                '$data.key2'
                            ),
                        ])
                    )->withData(new DataSetCollection([
                        '0' => [
                            'key1' => 'key1value1',
                            'key2' => 'key2value1',
                        ],
                        '1' => [
                            'key1' => 'key1value2',
                            'key2' => 'key2value2',
                        ],
                    ])),
                ])),
            ],
        ];
    }

    #[DataProvider('resolveThrowsExceptionDataProvider')]
    public function testResolveThrowsException(
        TestInterface $test,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        \Exception $expectedException
    ): void {
        try {
            $this->resolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);

            $this->fail('Exception not thrown');
        } catch (\Exception $exception) {
            $this->assertEquals($expectedException, $exception);
        }
    }

    /**
     * @return array<mixed>
     */
    public static function resolveThrowsExceptionDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'UnknownDataProviderException: test.data references a data provider that has not been defined' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'use' => 'step_import_name',
                        'data' => 'data_provider_import_name',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step(new ActionCollection([]), new AssertionCollection([])),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownItemException(
                        UnknownItemException::TYPE_DATASET,
                        'data_provider_import_name'
                    );
                    $exception->setStepName('step name');

                    return $exception;
                })(),
            ],
            'UnknownPageException: config.url references page not defined within a collection' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => '$page_import_name.url',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => new UnknownItemException(UnknownItemException::TYPE_PAGE, 'page_import_name'),
            ],
            'UnknownPageException: assertion string references page not defined within a collection' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'assertions' => [
                            '$page_import_name.elements.element_name exists',
                        ],
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownItemException(UnknownItemException::TYPE_PAGE, 'page_import_name');
                    $exception->setStepName('step name');
                    $exception->setContent('$page_import_name.elements.element_name exists');

                    return $exception;
                })(),
            ],
            'UnknownPageException: action string references page not defined within a collection' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'actions' => [
                            'click $page_import_name.elements.element_name',
                        ],
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownItemException(UnknownItemException::TYPE_PAGE, 'page_import_name');
                    $exception->setStepName('step name');
                    $exception->setContent('click $page_import_name.elements.element_name');

                    return $exception;
                })(),
            ],
            'UnknownPageElementException: test.elements references element that does not exist within a page' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'elements' => [
                            'non_existent' => '$page_import_name.elements.non_existent',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com'),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownPageElementException('page_import_name', 'non_existent');
                    $exception->setStepName('step name');

                    return $exception;
                })(),
            ],
            'UnknownPageElementException: assertion string references element that does not exist within a page' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'assertions' => [
                            '$page_import_name.elements.non_existent exists',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com'),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownPageElementException('page_import_name', 'non_existent');
                    $exception->setStepName('step name');
                    $exception->setContent('$page_import_name.elements.non_existent exists');

                    return $exception;
                })(),
            ],
            'UnknownPageElementException: action string references element that does not exist within a page' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'actions' => [
                            'click $page_import_name.elements.non_existent',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com'),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownPageElementException('page_import_name', 'non_existent');
                    $exception->setStepName('step name');
                    $exception->setContent('click $page_import_name.elements.non_existent');

                    return $exception;
                })(),
            ],
            'UnknownStepException: step.use references step not defined within a collection' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'use' => 'step_import_name',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownItemException(UnknownItemException::TYPE_STEP, 'step_import_name');
                    $exception->setStepName('step name');

                    return $exception;
                })(),
            ],
            'UnknownElementException: action element parameter references unknown step element' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'actions' => [
                            'click $elements.element_name',
                        ],
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownElementException('element_name');
                    $exception->setStepName('step name');
                    $exception->setContent('click $elements.element_name');

                    return $exception;
                })(),
            ],
            'UnknownElementException: assertion element parameter references unknown step element' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com/',
                    ],
                    'step name' => [
                        'assertions' => [
                            '$elements.element_name exists',
                        ],
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownElementException('element_name');
                    $exception->setStepName('step name');
                    $exception->setContent('$elements.element_name exists');

                    return $exception;
                })(),
            ],
        ];
    }
}
