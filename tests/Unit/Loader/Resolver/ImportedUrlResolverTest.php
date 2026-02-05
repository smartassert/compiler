<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Resolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Resolver\ImportedUrlResolver;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Provider\Page\EmptyPageProvider;
use webignition\BasilModels\Provider\Page\PageProvider;
use webignition\BasilModels\Provider\ProviderInterface;

class ImportedUrlResolverTest extends TestCase
{
    private ImportedUrlResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = ImportedUrlResolver::createResolver();
    }

    /**
     * @param non-empty-string $url
     */
    #[DataProvider('resolveDataProvider')]
    public function testResolve(string $url, ProviderInterface $pageProvider, string $expectedUrl): void
    {
        $resolvedUrl = $this->resolver->resolve($url, $pageProvider);

        $this->assertEquals($expectedUrl, $resolvedUrl);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveDataProvider(): array
    {
        return [
            'empty' => [
                'url' => '',
                'pageProvider' => new EmptyPageProvider(),
                'expectedUrl' => '',
            ],
            'literal url' => [
                'url' => 'http://example.com/',
                'pageProvider' => new EmptyPageProvider(),
                'expectedUrl' => 'http://example.com/',
            ],
            'well-formed page url reference' => [
                'url' => '$page_import_name.url',
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://page.example.com/'),
                ]),
                'expectedUrl' => 'http://page.example.com/',
            ],
        ];
    }
}
