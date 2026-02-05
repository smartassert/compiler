<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Exception\YamlLoaderException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

class YamlLoaderExceptionTest extends TestCase
{
    #[DataProvider('getPathForFileDoesNotExistExceptionDataProvider')]
    public function testGetPathForFileDoesNotExistException(string $path): void
    {
        $parseException = $this->createFileDoesNotExistParseException($path);

        if ($parseException instanceof ParseException) {
            $yamlLoaderException = YamlLoaderException::fromYamlParseException($parseException, $path);

            $this->assertSame($path, $yamlLoaderException->getPath());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function getPathForFileDoesNotExistExceptionDataProvider(): array
    {
        return [
            'integer-type path' => [
                'path' => '123',
            ],
        ];
    }

    private function createFileDoesNotExistParseException(string $path): ?ParseException
    {
        $yamlParser = new YamlParser();

        try {
            $yamlParser->parseFile($path);
        } catch (ParseException $parseException) {
            return $parseException;
        }

        return null;
    }
}
