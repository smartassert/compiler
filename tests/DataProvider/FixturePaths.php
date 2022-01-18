<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\DataProvider;

class FixturePaths
{
    public const BASE = '/tests/Fixtures/basil';
    public const TEST = '/tests/Fixtures/basil/Test';
    public const INVALID_TEST = '/tests/Fixtures/basil/InvalidTest';
    public const TARGET = '/tests/build/target';

    public static function getBase(): string
    {
        return getcwd() . self::BASE;
    }

    public static function getTest(): string
    {
        return getcwd() . self::TEST;
    }

    public static function getInvalidTest(): string
    {
        return getcwd() . self::INVALID_TEST;
    }

    public static function getTarget(): string
    {
        return getcwd() . self::TARGET;
    }
}
