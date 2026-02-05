<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Resolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Resolver\PageResolver;
use SmartAssert\Compiler\Loader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Model\Page\PageInterface;
use webignition\BasilModels\Parser\PageParser;

class PageResolverTest extends TestCase
{
    private PageResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = PageResolver::createResolver();
    }

    #[DataProvider('resolveSuccessDataProvider')]
    public function testResolveSuccess(
        PageInterface $page,
        PageInterface $expectedPage
    ): void {
        $resolvedPage = $this->resolver->resolve($page);

        $this->assertEquals($expectedPage, $resolvedPage);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveSuccessDataProvider(): array
    {
        $pageParser = PageParser::create();

        return [
            'no elements' => [
                'page' => $pageParser->parse('import_name', ['url' => 'http://example.com']),
                'expectedPage' => new Page('import_name', 'http://example.com', []),
            ],
            'element identifiers require no resolution' => [
                'page' => $pageParser->parse('import_name', [
                    'url' => 'http://example.com',
                    'elements' => [
                        'form' => '$".form"',
                    ],
                ]),
                'expectedPage' => new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                ]),
            ],
            'direct parent reference' => [
                'page' => $pageParser->parse('import_name', [
                    'url' => 'http://example.com',
                    'elements' => [
                        'form' => '$".form"',
                        'form_container' => '$form >> $".container"',
                    ],
                ]),
                'expectedPage' => new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'form_container' => '$".form" >> $".container"',
                ]),
            ],
            'indirect parent reference, defined in order' => [
                'page' => $pageParser->parse('import_name', [
                    'url' => 'http://example.com',
                    'elements' => [
                        'form' => '$".form"',
                        'form_container' => '$form >> $".container"',
                        'form_input' => '$form_container >> $".input"',
                    ],
                ]),
                'expectedPage' => new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'form_container' => '$".form" >> $".container"',
                    'form_input' => '$".form" >> $".container" >> $".input"',
                ]),
            ],
            'indirect parent reference, defined in out of order' => [
                'page' => $pageParser->parse('import_name', [
                    'url' => 'http://example.com',
                    'elements' => [
                        'form' => '$".form"',
                        'form_input' => '$".form" >> $".container" >> $".input"',
                        'form_container' => '$".form" >> $".container"',
                    ],
                ]),
                'expectedPage' => new Page('import_name', 'http://example.com', [
                    'form' => '$".form"',
                    'form_container' => '$".form" >> $".container"',
                    'form_input' => '$".form" >> $".container" >> $".input"',
                ]),
            ],
        ];
    }

    public function testResolveUnresolvableReference(): void
    {
        $pageParser = PageParser::create();

        $page = $pageParser->parse('import_name', [
            'url' => 'http://example.com',
            'elements' => [
                'form' => '$".form"',
                'unresolvable' => '$missing >> $".button"',
            ],
        ]);

        $this->expectExceptionObject(new UnknownPageElementException('import_name', 'missing'));

        $this->resolver->resolve($page);
    }
}
