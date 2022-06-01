<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

class EnvironmentValue
{
    private const PREFIX = '$env.';

    public function __construct(
        private readonly string $property,
        private readonly ?string $default = null
    ) {
    }

    public static function is(string $value): bool
    {
        return preg_match('/^' . preg_quote(self::PREFIX, '/') . '.+/', $value) > 0;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }
}
