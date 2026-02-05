<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader;

use SmartAssert\Compiler\Loader\Exception\YamlLoaderException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

class YamlLoader
{
    public function __construct(
        private YamlParser $yamlParser
    ) {}

    public static function createLoader(): YamlLoader
    {
        return new YamlLoader(
            new YamlParser()
        );
    }

    /**
     * @return array<mixed>
     *
     * @throws YamlLoaderException
     */
    public function loadArray(string $path): array
    {
        try {
            $data = $this->yamlParser->parseFile($path);
        } catch (ParseException $parseException) {
            throw YamlLoaderException::fromYamlParseException($parseException, $path);
        }

        if (is_string($data) && '' === trim($data)) {
            $data = null;
        }

        if (null === $data) {
            $data = [];
        }

        if (!is_array($data)) {
            throw YamlLoaderException::createDataIsNotAnArrayException($path);
        }

        return $data;
    }
}
