<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use SmartAssert\Compiler\Loader\Exception\YamlLoaderException;
use SmartAssert\Compiler\Tests\Services\FixturePathFinder;
use SmartAssert\Compiler\Loader\YamlLoader;

class YamlLoaderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }

    public function testLoadArrayYamlParserThrowsException(): void
    {
        $path = 'file.yml';
        $exceptionMessage = 'exception message content';

        $parseException = new ParseException($exceptionMessage);

        $yamlParser = \Mockery::mock(YamlParser::class);
        $yamlParser
            ->shouldReceive('parseFile')
            ->with($path)
            ->andThrow($parseException)
        ;

        $yamlLoader = new YamlLoader($yamlParser);

        $this->expectException(YamlLoaderException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $yamlLoader->loadArray($path);
    }

    public function testLoadArrayThrowsDataIsNotAnArrayException(): void
    {
        $path = 'file.yml';

        $yamlParser = \Mockery::mock(YamlParser::class);
        $yamlParser
            ->shouldReceive('parseFile')
            ->with($path)
            ->andReturn(1)
        ;

        $yamlLoader = new YamlLoader($yamlParser);

        $this->expectException(YamlLoaderException::class);
        $this->expectExceptionMessage(YamlLoaderException::MESSAGE_DATA_IS_NOT_AN_ARRAY);
        $this->expectExceptionCode(YamlLoaderException::CODE_DATA_IS_NOT_AN_ARRAY);

        $yamlLoader->loadArray($path);
    }

    #[DataProvider('loadArrayWithEmptyContentDataProvider')]
    public function testLoadArrayWithEmptyContent(string $path): void
    {
        $yamlLoader = YamlLoader::createLoader();

        $data = $yamlLoader->loadArray($path);

        $this->assertSame([], $data);
    }

    /**
     * @return array<mixed>
     */
    public static function loadArrayWithEmptyContentDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('basil/Empty/empty.yml'),
            ],
            'whitespace' => [
                'path' => FixturePathFinder::find('basil/Empty/whitespace.yml'),
            ],
            'null, canonical' => [
                'path' => FixturePathFinder::find('basil/Empty/null-canonical.yml'),
            ],
            'null, non-canonical' => [
                'path' => FixturePathFinder::find('basil/Empty/null-non-canonical.yml'),
            ],
        ];
    }
}
