<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

class AccessorDefaultValueFactory
{
    public function __construct(
        private SingleQuotedStringEscaper $singleQuotedStringEscaper,
        private EnvironmentValueFactory $environmentValueFactory
    ) {
    }

    public static function createFactory(): AccessorDefaultValueFactory
    {
        return new AccessorDefaultValueFactory(
            SingleQuotedStringEscaper::create(),
            EnvironmentValueFactory::createFactory()
        );
    }

    public function createInteger(string $value): ?int
    {
        $value = $this->create($value, function (string $valueDefault): ?int {
            return ctype_digit($valueDefault)
                ? (int) $valueDefault
                : null;
        });

        return null === $value ? $value : (int) $value;
    }

    public function createString(string $value): ?string
    {
        $value = $this->create($value, function (string $valueDefault): string {
            return "'" . $this->singleQuotedStringEscaper->escape($valueDefault) . "'";
        });

        return null === $value ? $value : (string) $value;
    }

    private function create(string $value, callable $defaultValueHandler): string|int|null
    {
        if (EnvironmentValue::is($value)) {
            $environmentValue = $this->environmentValueFactory->create($value);
            $valueDefault = $environmentValue->getDefault();

            if (null !== $valueDefault) {
                return $defaultValueHandler($valueDefault);
            }
        }

        return null;
    }
}
