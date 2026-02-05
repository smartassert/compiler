<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Resolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Resolver\StepResolver;
use SmartAssert\Compiler\Loader\Resolver\UnknownElementException;
use SmartAssert\Compiler\Loader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Model\Statement\Action\ActionCollection;
use webignition\BasilModels\Model\Statement\Action\ResolvedAction;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollection;
use webignition\BasilModels\Model\Statement\Assertion\ResolvedAssertion;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilModels\Parser\StepParser;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Page\EmptyPageProvider;
use webignition\BasilModels\Provider\Page\PageProvider;
use webignition\BasilModels\Provider\Page\PageProviderInterface;

class StepResolverTest extends TestCase
{
    private StepResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = StepResolver::createResolver();
    }

    #[DataProvider('resolveForPendingImportResolutionStepDataProvider')]
    #[DataProvider('resolveActionsAndAssertionsDataProvider')]
    #[DataProvider('resolveIdentifierCollectionDataProvider')]
    public function testResolveSuccess(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ): void {
        $resolvedStep = $this->resolver->resolve($step, $pageProvider);

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveForPendingImportResolutionStepDataProvider(): array
    {
        return [
            'pending import step: has step import name' => [
                'step' => self::createStep([
                    'use' => 'import_name',
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withImportName('import_name'),
            ],
            'pending import step: has data provider import name' => [
                'step' => self::createStep([
                    'data' => 'data_import_name',
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withDataImportName('data_import_name'),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function resolveActionsAndAssertionsDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $nonResolvableStep = self::createStep([
            'actions' => [
                'wait 30',
            ],
            'assertions' => [
                '$".selector" exists',
            ],
        ]);

        return [
            'non-resolvable actions, non-resolvable assertions' => [
                'step' => $nonResolvableStep,
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => $nonResolvableStep
            ],
            'page element reference in action identifier' => [
                'step' => self::createStep([
                    'actions' => [
                        'set $page_import_name.elements.examined to "value"',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com/',
                        [
                            'examined' => '$".examined"',
                        ]
                    )
                ]),
                'expectedStep' => new Step(
                    new ActionCollection([
                        new ResolvedAction(
                            $actionParser->parse('set $page_import_name.elements.examined to "value"', 0),
                            '$".examined"',
                            '"value"'
                        ),
                    ]),
                    new AssertionCollection([])
                ),
            ],
            'page element reference in action value' => [
                'step' => self::createStep([
                    'actions' => [
                        'set $".examined" to $page_import_name.elements.expected',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com/',
                        [
                            'expected' => '$".expected"',
                        ]
                    )
                ]),
                'expectedStep' => new Step(
                    new ActionCollection([
                        new ResolvedAction(
                            $actionParser->parse('set $".examined" to $page_import_name.elements.expected', 0),
                            '$".examined"',
                            '$".expected"'
                        ),
                    ]),
                    new AssertionCollection([])
                ),
            ],
            'page element reference in assertion examined value' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$page_import_name.elements.examined exists',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com/',
                        [
                            'examined' => '$".examined"',
                        ]
                    )
                ]),
                'expectedStep' => new Step(
                    new ActionCollection([]),
                    new AssertionCollection([
                        new ResolvedAssertion(
                            $assertionParser->parse('$page_import_name.elements.examined exists', 0),
                            '$".examined"'
                        ),
                    ])
                ),
            ],
            'page element reference in assertion expected value' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$".examined" is $page_import_name.elements.expected ',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com/',
                        [
                            'expected' => '$".expected"',
                        ]
                    )
                ]),
                'expectedStep' => new Step(
                    new ActionCollection([]),
                    new AssertionCollection([
                        new ResolvedAssertion(
                            $assertionParser->parse('$".examined" is $page_import_name.elements.expected', 0),
                            '$".examined"',
                            '$".expected"'
                        ),
                    ])
                ),
            ],
            'element reference in action identifier' => [
                'step' => self::createStep([
                    'actions' => [
                        'set $elements.examined to "value"',
                    ],
                    'elements' => [
                        'examined' => '$".examined"',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    new ActionCollection([
                        new ResolvedAction(
                            $actionParser->parse('set $elements.examined to "value"', 0),
                            '$".examined"',
                            '"value"'
                        ),
                    ]),
                    new AssertionCollection([]),
                )->withIdentifiers([
                    'examined' => '$".examined"',
                ]),
            ],
            'element reference in action value' => [
                'step' => self::createStep([
                    'actions' => [
                        'set $".examined" to $elements.expected',
                    ],
                    'elements' => [
                        'expected' => '$".expected"',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    new ActionCollection([
                        new ResolvedAction(
                            $actionParser->parse('set $".examined" to $elements.expected', 0),
                            '$".examined"',
                            '$".expected"'
                        ),
                    ]),
                    new AssertionCollection([]),
                )->withIdentifiers([
                    'expected' => '$".expected"',
                ]),
            ],
            'attribute reference in action value' => [
                'step' => self::createStep([
                    'actions' => [
                        'set $".examined" to $elements.expected.attribute_name',
                    ],
                    'elements' => [
                        'expected' => '$".expected"',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    new ActionCollection([
                        new ResolvedAction(
                            $actionParser->parse('set $".examined" to $elements.expected.attribute_name', 0),
                            '$".examined"',
                            '$".expected".attribute_name'
                        ),
                    ]),
                    new AssertionCollection([]),
                )->withIdentifiers([
                    'expected' => '$".expected"'
                ]),
            ],
            'element reference in assertion examined value' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$elements.examined exists',
                    ],
                    'elements' => [
                        'examined' => '$".examined"'
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    new ActionCollection([]),
                    new AssertionCollection([
                        new ResolvedAssertion(
                            $assertionParser->parse('$elements.examined exists', 0),
                            '$".examined"'
                        ),
                    ])
                )->withIdentifiers([
                    'examined' => '$".examined"'
                ]),
            ],
            'element reference in assertion expected value' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$".examined-selector" is $elements.expected',
                    ],
                    'elements' => [
                        'expected' => '$".expected"'
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    new ActionCollection([]),
                    new AssertionCollection([
                        new ResolvedAssertion(
                            $assertionParser->parse('$".examined-selector" is $elements.expected', 0),
                            '$".examined-selector"',
                            '$".expected"'
                        ),
                    ])
                )->withIdentifiers([
                    'expected' => '$".expected"'
                ]),
            ],
            'attribute reference in assertion examined value' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$elements.examined.attribute_name exists',
                    ],
                    'elements' => [
                        'examined' => '$".examined"'
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    new ActionCollection([]),
                    new AssertionCollection([
                        new ResolvedAssertion(
                            $assertionParser->parse('$elements.examined.attribute_name exists', 0),
                            '$".examined".attribute_name'
                        ),
                    ])
                )->withIdentifiers([
                    'examined' => '$".examined"'
                ]),
            ],
            'attribute reference in assertion expected value' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$".examined" is $elements.expected.attribute_name',
                    ],
                    'elements' => [
                        'expected' => '$".expected"'
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    new ActionCollection([]),
                    new AssertionCollection([
                        new ResolvedAssertion(
                            $assertionParser->parse('$".examined" is $elements.expected.attribute_name', 0),
                            '$".examined"',
                            '$".expected".attribute_name'
                        ),
                    ])
                )->withIdentifiers([
                    'expected' => '$".expected"'
                ]),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function resolveIdentifierCollectionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'no resolvable element identifiers' => [
                'step' => self::createStep([
                    'elements' => [
                        'name' => '$".selector"',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withIdentifiers([
                        'name' => '$".selector"',
                    ]),
            ],
            'identifier with page element references, unused by actions or assertions' => [
                'step' => self::createStep([
                    'elements' => [
                        'step_element_name' => '$page_import_name.elements.page_element_name',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com/',
                        [
                            'page_element_name' => '$".resolved"',
                        ]
                    )
                ]),
                'expectedStep' => new Step(new ActionCollection([]), new AssertionCollection([]))
                    ->withIdentifiers([
                        'step_element_name' => '$".resolved"',
                    ]),
            ],
            'identifier with page element references, used by actions and assertions' => [
                'step' => self::createStep([
                    'actions' => [
                        'click $page_import_name.elements.page_element_name',
                    ],
                    'assertions' => [
                        '$page_import_name.elements.page_element_name exists',
                    ],
                    'elements' => [
                        'step_element_name' => '$page_import_name.elements.page_element_name',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com/',
                        [
                            'page_element_name' => '$".resolved"',
                        ]
                    )
                ]),
                'expectedStep' => new Step(
                    new ActionCollection([
                        new ResolvedAction(
                            $actionParser->parse('click $page_import_name.elements.page_element_name', 0),
                            '$".resolved"',
                        ),
                    ]),
                    new AssertionCollection([
                        new ResolvedAssertion(
                            $assertionParser->parse('$page_import_name.elements.page_element_name exists', 1),
                            '$".resolved"'
                        ),
                    ])
                )
                    ->withIdentifiers([])
                    ->withIdentifiers([
                        'step_element_name' => '$".resolved"',
                    ])
            ],
        ];
    }

    #[DataProvider('resolvePageElementReferencesThrowsExceptionDataProvider')]
    public function testResolvePageElementReferencesThrowsException(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        \Exception $expectedException
    ): void {
        try {
            $this->resolver->resolve($step, $pageProvider);

            $this->fail('Exception not thrown');
        } catch (\Exception $contextAwareException) {
            $this->assertEquals($expectedException, $contextAwareException);
        }
    }

    /**
     * @return array<mixed>
     */
    public static function resolvePageElementReferencesThrowsExceptionDataProvider(): array
    {
        return [
            'UnknownPageElementException: action has page element reference, referenced page lacks element' => [
                'step' => self::createStep([
                    'actions' => [
                        'click $page_import_name.elements.element_name',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com/'),
                ]),
                'expectedException' => (function () {
                    $exception = new UnknownPageElementException('page_import_name', 'element_name');
                    $exception->setContent('click $page_import_name.elements.element_name');

                    return $exception;
                })(),
            ],
            'UnknownPageElementException: assertion has page element reference, referenced page lacks element' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$page_import_name.elements.element_name exists',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com/'),
                ]),
                'expectedException' => (function () {
                    $exception = new UnknownPageElementException('page_import_name', 'element_name');
                    $exception->setContent('$page_import_name.elements.element_name exists');

                    return $exception;
                })(),
            ],
            'UnknownPageException: action has page element reference, page does not exist' => [
                'step' => self::createStep([
                    'actions' => [
                        'click $page_import_name.elements.element_name',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownItemException(UnknownItemException::TYPE_PAGE, 'page_import_name');
                    $exception->setContent('click $page_import_name.elements.element_name');

                    return $exception;
                })(),
            ],
            'UnknownPageException: assertion has page element reference, page does not exist' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$page_import_name.elements.element_name exists',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownItemException(UnknownItemException::TYPE_PAGE, 'page_import_name');
                    $exception->setContent('$page_import_name.elements.element_name exists');

                    return $exception;
                })(),
            ],
            'UnknownElementException: action has element reference, element missing' => [
                'step' => self::createStep([
                    'actions' => [
                        'click $elements.element_name',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownElementException('element_name');
                    $exception->setContent('click $elements.element_name');

                    return $exception;
                })(),
            ],
            'UnknownElementException: assertion has page element reference, referenced page invalid' => [
                'step' => self::createStep([
                    'assertions' => [
                        '$elements.element_name exists',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedException' => (function () {
                    $exception = new UnknownElementException('element_name');
                    $exception->setContent('$elements.element_name exists');

                    return $exception;
                })(),
            ],
        ];
    }

    /**
     * @param array<mixed> $stepData
     */
    private static function createStep(array $stepData): StepInterface
    {
        return StepParser::create()->parse($stepData);
    }
}
