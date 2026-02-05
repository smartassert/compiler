<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Resolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Resolver\StatementResolver;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Model\Statement\Action\ResolvedAction;
use webignition\BasilModels\Model\Statement\Assertion\ResolvedAssertion;
use webignition\BasilModels\Model\Statement\StatementInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilModels\Provider\Identifier\EmptyIdentifierProvider;
use webignition\BasilModels\Provider\Identifier\IdentifierProvider;
use webignition\BasilModels\Provider\Identifier\IdentifierProviderInterface;
use webignition\BasilModels\Provider\Page\EmptyPageProvider;
use webignition\BasilModels\Provider\Page\PageProvider;
use webignition\BasilModels\Provider\Page\PageProviderInterface;

class StatementResolverTest extends TestCase
{
    private StatementResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = StatementResolver::createResolver();
    }

    #[DataProvider('resolveAlreadyResolvedActionDataProvider')]
    #[DataProvider('resolveAlreadyResolvedAssertionDataProvider')]
    public function testResolveAlreadyResolved(StatementInterface $statement): void
    {
        $resolvedStatement = $this->resolver->resolve(
            $statement,
            new EmptyPageProvider(),
            new EmptyIdentifierProvider()
        );

        $this->assertSame($statement, $resolvedStatement);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveAlreadyResolvedActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action' => [
                'statement' => $actionParser->parse('click $".selector"', 0),
            ],
            'input action' => [
                'statement' => $actionParser->parse('set $".selector" to "value"', 0),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function resolveAlreadyResolvedAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'exists assertion' => [
                'statement' => $assertionParser->parse('$".selector" exists', 0),
            ],
            'comparison assertion' => [
                'statement' => $assertionParser->parse('$".selector" is "value"', 0),
            ],
        ];
    }

    #[DataProvider('resolveIsResolvedActionDataProvider')]
    #[DataProvider('resolveIsResolvedAssertionDataProvider')]
    public function testResolveIsResolved(
        StatementInterface $statement,
        PageProviderInterface $pageProvider,
        IdentifierProviderInterface $identifierProvider,
        StatementInterface $expectedStatement
    ): void {
        $resolvedAssertion = $this->resolver->resolve($statement, $pageProvider, $identifierProvider);

        $this->assertEquals($expectedStatement, $resolvedAssertion);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveIsResolvedActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action with element reference identifier' => [
                'statement' => $actionParser->parse('click $elements.element_name', 0),
                'pageProvider' => new EmptyPageProvider(),
                'identifierProvider' => new IdentifierProvider([
                    'element_name' => '$".selector"',
                ]),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse('click $elements.element_name', 0),
                    '$".selector"'
                ),
            ],
            'interaction action with page element reference identifier' => [
                'statement' => $actionParser->parse('click $page_import_name.elements.element_name', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'element_name' => '$".selector"',
                        ]
                    ),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse('click $page_import_name.elements.element_name', 0),
                    '$".selector"'
                ),
            ],
            'input action with element reference identifier and literal value' => [
                'statement' => $actionParser->parse('set $elements.element_name to "value"', 0),
                'pageProvider' => new EmptyPageProvider(),
                'identifierProvider' => new IdentifierProvider([
                    'element_name' => '$".selector"',
                ]),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse('set $elements.element_name to "value"', 0),
                    '$".selector"',
                    '"value"'
                ),
            ],
            'input action with page element reference identifier and literal value' => [
                'statement' => $actionParser->parse('set $page_import_name.elements.element_name to "value"', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'element_name' => '$".selector"',
                        ]
                    ),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse('set $page_import_name.elements.element_name to "value"', 0),
                    '$".selector"',
                    '"value"'
                ),
            ],
            'input action with dom identifier and element reference value' => [
                'statement' => $actionParser->parse('set $".selector" to $elements.element_name', 0),
                'pageProvider' => new EmptyPageProvider(),
                'identifierProvider' => new IdentifierProvider([
                    'element_name' => '$".resolved"',
                ]),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse('set $".selector" to $elements.element_name', 0),
                    '$".selector"',
                    '$".resolved"'
                ),
            ],
            'input action with dom identifier and page element reference value' => [
                'statement' => $actionParser->parse('set $".selector" to $page_import_name.elements.element_name', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'element_name' => '$".resolved"',
                        ]
                    ),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse('set $".selector" to $page_import_name.elements.element_name', 0),
                    '$".selector"',
                    '$".resolved"'
                ),
            ],
            'input action with element reference identifier and element reference value' => [
                'statement' => $actionParser->parse('set $elements.element_one to $elements.element_two', 0),
                'pageProvider' => new EmptyPageProvider(),
                'identifierProvider' => new IdentifierProvider([
                    'element_one' => '$".one"',
                    'element_two' => '$".two"',
                ]),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse('set $elements.element_one to $elements.element_two', 0),
                    '$".one"',
                    '$".two"'
                ),
            ],
            'input action with page element reference identifier and page element reference value' => [
                'statement' => $actionParser->parse(
                    'set $page_import_name.elements.element_one to $page_import_name.elements.element_two',
                    0,
                ),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'element_one' => '$".one"',
                            'element_two' => '$".two"',
                        ]
                    ),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse(
                        'set $page_import_name.elements.element_one to $page_import_name.elements.element_two',
                        0,
                    ),
                    '$".one"',
                    '$".two"'
                ),
            ],
            'input action with dom identifier and imported page url value' => [
                'statement' => $actionParser->parse('set $".selector" to $page_import_name.url', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com'),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAction(
                    $actionParser->parse('set $".selector" to $page_import_name.url', 0),
                    '$".selector"',
                    '"http://example.com"'
                ),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function resolveIsResolvedAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'exists assertion with element reference identifier' => [
                'statement' => $assertionParser->parse('$elements.element_name exists', 0),
                'pageProvider' => new EmptyPageProvider(),
                'identifierProvider' => new IdentifierProvider([
                    'element_name' => '$".selector"',
                ]),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$elements.element_name exists', 0),
                    '$".selector"'
                ),
            ],
            'exists assertion with page element reference identifier' => [
                'statement' => $assertionParser->parse('$page_import_name.elements.element_name exists', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'element_name' => '$".selector"',
                        ]
                    ),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$page_import_name.elements.element_name exists', 0),
                    '$".selector"'
                ),
            ],
            'is assertion with element reference identifier and literal value' => [
                'statement' => $assertionParser->parse('$elements.element_name is "value"', 0),
                'pageProvider' => new EmptyPageProvider(),
                'identifierProvider' => new IdentifierProvider([
                    'element_name' => '$".selector"',
                ]),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$elements.element_name is "value"', 0),
                    '$".selector"',
                    '"value"'
                ),
            ],
            'is assertion with page element reference identifier and literal value' => [
                'statement' => $assertionParser->parse('$page_import_name.elements.element_name is "value"', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'element_name' => '$".selector"',
                        ]
                    ),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$page_import_name.elements.element_name is "value"', 0),
                    '$".selector"',
                    '"value"'
                ),
            ],
            'is assertion with dom identifier and element reference value' => [
                'statement' => $assertionParser->parse('$".selector" is $elements.element_name', 0),
                'pageProvider' => new EmptyPageProvider(),
                'identifierProvider' => new IdentifierProvider([
                    'element_name' => '$".resolved"',
                ]),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$".selector" is $elements.element_name', 0),
                    '$".selector"',
                    '$".resolved"'
                ),
            ],
            'is assertion with dom identifier and page element reference value' => [
                'statement' => $assertionParser->parse('$".selector" is $page_import_name.elements.element_name', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'element_name' => '$".resolved"',
                        ]
                    ),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$".selector" is $page_import_name.elements.element_name', 0),
                    '$".selector"',
                    '$".resolved"'
                ),
            ],
            'is assertion with element reference identifier and element reference value' => [
                'statement' => $assertionParser->parse('$elements.element_one is $elements.element_two', 0),
                'pageProvider' => new EmptyPageProvider(),
                'identifierProvider' => new IdentifierProvider([
                    'element_one' => '$".one"',
                    'element_two' => '$".two"',
                ]),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$elements.element_one is $elements.element_two', 0),
                    '$".one"',
                    '$".two"'
                ),
            ],
            'is assertion with page element reference identifier and page element reference value' => [
                'statement' => $assertionParser->parse(
                    '$page_import_name.elements.element_one is $page_import_name.elements.element_two',
                    0,
                ),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'element_one' => '$".one"',
                            'element_two' => '$".two"',
                        ]
                    ),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse(
                        '$page_import_name.elements.element_one is $page_import_name.elements.element_two',
                        0,
                    ),
                    '$".one"',
                    '$".two"'
                ),
            ],
            'is assertion with literal identifier and imported page url value' => [
                'statement' => $assertionParser->parse('$page.url is $page_import_name.url', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com'),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$page.url is $page_import_name.url', 0),
                    '$page.url',
                    '"http://example.com"'
                ),
            ],
            'is assertion with page url identifier and literal value' => [
                'statement' => $assertionParser->parse('$page_import_name.url is "http://example.com"', 0),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com'),
                ]),
                'identifierProvider' => new EmptyIdentifierProvider(),
                'expectedStatement' => new ResolvedAssertion(
                    $assertionParser->parse('$page_import_name.url is "http://example.com"', 0),
                    '"http://example.com"',
                    '"http://example.com"'
                ),
            ],
        ];
    }
}
