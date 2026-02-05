<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Exception\InvalidPageException;
use SmartAssert\Compiler\Loader\PageLoader;
use SmartAssert\Compiler\Loader\Validator\InvalidResult;
use SmartAssert\Compiler\Loader\Validator\PageValidator;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Model\Page\PageInterface;

class PageLoaderTest extends TestCase
{
    private PageLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = PageLoader::createLoader();
    }

    #[DataProvider('loadSuccessDataProvider')]
    public function testLoadSuccess(string $importName, string $path, PageInterface $expectedPage): void
    {
        $page = $this->loader->load($importName, $path);

        $this->assertEquals($expectedPage, $page);
    }

    /**
     * @return array<mixed>
     */
    public static function loadSuccessDataProvider(): array
    {
        return [
            'url only' => [
                'importName' => 'import_name',
                'path' => FixturePathFinder::find('basil/Page/example.com.url-only.yml'),
                'expectedPage' => new Page('import_name', 'https://example.com'),
            ],
            'url and element references' => [
                'importName' => 'import_name',
                'path' => FixturePathFinder::find('basil/Page/example.com.form.yml'),
                'expectedPage' => new Page(
                    'import_name',
                    'https://example.com',
                    [
                        'form' => '$".form"',
                        'input' => '$form >> $".input"',
                    ]
                ),
            ],
        ];
    }

    public function testLoadThrowsInvalidPageException(): void
    {
        $importName = 'page_import_name';
        $path = FixturePathFinder::find('basil/Empty/empty.yml');

        try {
            $this->loader->load($importName, $path);

            $this->fail('Exception not thrown');
        } catch (InvalidPageException $invalidPageException) {
            $expectedException = new InvalidPageException($importName, $path, new InvalidResult(
                [
                    'import_name' => $importName,
                    'path' => $path,
                    'data' => [],
                ],
                ResultType::PAGE,
                PageValidator::REASON_URL_EMPTY
            ));

            $this->assertEquals($expectedException, $invalidPageException);
        }
    }
}
