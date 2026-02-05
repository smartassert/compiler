<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Resolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Resolver\PageElementReferenceResolver;
use SmartAssert\Compiler\Loader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Provider\Page\PageProvider;
use webignition\BasilModels\Provider\ProviderInterface;

class PageElementReferenceResolverTest extends TestCase
{
    private PageElementReferenceResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = PageElementReferenceResolver::createResolver();
    }

    #[DataProvider('resolveIsResolvedDataProvider')]
    public function testResolveIsResolved(
        string $pageElementReference,
        ProviderInterface $pageProvider,
        string $expectedIdentifier
    ): void {
        $identifier = $this->resolver->resolve($pageElementReference, $pageProvider);

        $this->assertEquals($expectedIdentifier, $identifier);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveIsResolvedDataProvider(): array
    {
        return [
            'element reference' => [
                'pageElementReference' => '$page_import_name.elements.element_name',
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com/',
                        [
                            'element_name' => '$".selector"',
                        ]
                    )
                ]),
                'expectedIdentifier' => '$".selector"',
            ],
            'attribute reference' => [
                'pageElementReference' => '$page_import_name.elements.element_name.attribute_name',
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com/',
                        [
                            'element_name' => '$".selector"',
                        ]
                    )
                ]),
                'expectedIdentifier' => '$".selector".attribute_name',
            ],
        ];
    }

    #[DataProvider('resolveThrowsUnknownPageElementExceptionDataProvider')]
    public function testResolveThrowsUnknownPageElementException(
        string $pageElementReference,
        ProviderInterface $pageProvider,
        string $expectedExceptionMessage
    ): void {
        $this->expectException(UnknownPageElementException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->resolver->resolve($pageElementReference, $pageProvider);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveThrowsUnknownPageElementExceptionDataProvider(): array
    {
        return [
            'element not present in page' => [
                'pageElementReference' => '$page_import_name.elements.element_name',
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com/')
                ]),
                'expectedExceptionMessage' => 'Unknown page element "element_name" in page "page_import_name"',
            ],
            'parent element not present in page' => [
                'pageElementReference' => '$page_import_name.elements.element_name',
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com/', [
                        'element_name' => '$parent_element_name >> $".element"',
                    ])
                ]),
                'expectedExceptionMessage' => 'Unknown page element "parent_element_name" in page "page_import_name"',
            ],
        ];
    }
}
