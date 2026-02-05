<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Services;

class FixturePathFinder
{
    private const FIXTURES_RELATIVE_PATH = '/../Fixtures';

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    public static function find(string $path): string
    {
        $realpath = realpath(self::getBasePath() . '/' . $path);

        if (false === $realpath) {
            throw new \RuntimeException('Fixture "' . $path . '" does not exist');
        }

        return $realpath;
    }

    public static function getBasePath(): string
    {
        return __DIR__ . self::FIXTURES_RELATIVE_PATH;
    }
}
